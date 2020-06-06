<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Model;

use Braintree\Exception;
use Magento\Framework\Validator\Exception as ValidatorException;

class Import
{
    const CONFIG_MAX_LIMIT_IN_PART = 'amasty/import/limit';
    const MAX_ERRORS_IN_FILE = 1000;
    const REPLACE_CSV = 'replace.csv';

    /**
     * @var \Amasty\Finder\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Amasty\Finder\Api\ValueRepositoryInterface
     */
    private $valueRepository;

    /**
     * @var \Amasty\Finder\Api\MapRepositoryInterface
     */
    private $mapRepository;

    /**
     * @var \Amasty\Finder\Api\ImportLogRepositoryInterface
     */
    private $importLogRepository;

    /**
     * @var \Amasty\Finder\Api\ImportHistoryRepositoryInterface
     */
    private $importHistoryLogRepository;

    /**
     * @var ImportErrors
     */
    private $importErrorModel;

    /**
     * @var \Amasty\Finder\Helper\Import
     */
    private $helper;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * Import constructor.
     * @param \Amasty\Finder\Api\FinderRepositoryInterface $finderRepository
     * @param \Amasty\Finder\Api\ValueRepositoryInterface $valueRepository
     * @param \Amasty\Finder\Api\MapRepositoryInterface $mapRepository
     * @param \Amasty\Finder\Api\ImportLogRepositoryInterface $importLogRepository
     * @param \Amasty\Finder\Api\ImportHistoryRepositoryInterface $importHistoryLogRepository
     * @param ImportErrors $importErrorModel
     * @param \Amasty\Finder\Helper\Config $configHelper
     * @param \Magento\Framework\File\UploaderFactory $uploader
     * @param \Amasty\Finder\Helper\Import $helper
     */
    public function __construct(
        \Amasty\Finder\Api\FinderRepositoryInterface $finderRepository,
        \Amasty\Finder\Api\ValueRepositoryInterface $valueRepository,
        \Amasty\Finder\Api\MapRepositoryInterface $mapRepository,
        \Amasty\Finder\Api\ImportLogRepositoryInterface $importLogRepository,
        \Amasty\Finder\Api\ImportHistoryRepositoryInterface $importHistoryLogRepository,
        \Amasty\Finder\Model\ImportErrors $importErrorModel,
        \Amasty\Finder\Helper\Config $configHelper,
        \Magento\Framework\File\UploaderFactory $uploader,
        \Amasty\Finder\Helper\Import $helper
    ) {
        $this->configHelper = $configHelper;
        $this->valueRepository = $valueRepository;
        $this->mapRepository = $mapRepository;
        $this->finderRepository = $finderRepository;
        $this->importLogRepository = $importLogRepository;
        $this->importHistoryLogRepository = $importHistoryLogRepository;
        $this->importErrorModel = $importErrorModel;
        $this->helper = $helper;
        $this->uploaderFactory = $uploader;
    }

    /**
     * @param $fileLog
     * @return bool
     */
    protected function validateImportFile($fileLog)
    {
        if ($fileLog->getIsLocked() == 1) {
            return false;
        }

        if ($fileLog->getStatus() == \Amasty\Finder\Model\ImportLog::STATE_UPLOADED) {
            $fileLog->setStartedAt(date('Y-m-d H:i:s'));
        }

        $isLast = $fileLog->getLastStartProcessingLine() == $fileLog->getCountProcessingLines();
        if ($fileLog->getLastStartProcessingLine() != 0 && $isLast) {
            $this->importErrorModel->error($fileLog->getId(), 0, 'Error! File is executing the second time');
            $fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
            return false;
        }

        if (!is_file($fileLog->getFilePath())) {
            $this->importErrorModel->error($fileLog->getId(), 0, 'File not exists');
            $fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
            return false;
        }
        return true;
    }

    /**
     * @param $cnt
     * @param $fileLog
     * @param $currentLine
     * @return bool
     */
    private function validateRange($cnt, $fileLog, $currentLine)
    {
        $cntRange = 1;
        foreach ($cnt as $count) {
            if ($count) {
                $cntRange *= $count;
            }
        }

        if ($cntRange >= $this->configHelper->getConfigValue('import/max_rows_per_import')) {
            $this->importErrorModel->error(
                $fileLog->getId(),
                $currentLine,
                'Line #' . $currentLine . ' contains big range!'
            );
            $fileLog->error();
            return false;
        }

        return true;
    }

    /**
     * @param $fileLog
     * @param $countProcessedRows
     * @return bool|int
     */
    public function runFile($fileLog, &$countProcessedRows)
    {
        ini_set('auto_detect_line_endings', true);
        $fileName = $fileLog->getFileName();
        $finderId = $fileLog->getFinderId();
        $filePath = $fileLog->getFilePath();

        if (!$this->validateImportFile($fileLog)) {
            return 0;
        }

        $fp = fopen($filePath, 'r');

        if (!$fp) {
            $this->importErrorModel->error($fileLog->getId(), 0, 'Can not open file');
            $fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
            return 0;
        }

        $fileLog->setIsLocked(1);
        $fileLog->setLastStartProcessingLine($fileLog->getCountProcessingLines());
        if ($fileLog->getStatus() == \Amasty\Finder\Model\ImportLog::STATE_UPLOADED) {
            $countLines = $this->countLines($fp);
            $fileLog->setCountLines($countLines);
            $fileLog->setStatus(\Amasty\Finder\Model\ImportLog::STATE_PROCESSING);
        }
        $fileLog->save();

        $finder = $this->finderRepository->getById($finderId);

        if (!$finder->getId()) {
            $this->importErrorModel->error($fileLog->getId(), 0, 'Finder id ' . $finderId . ' does not exists');
            $fileLog->setIsLocked(0)->error()->save();
            return 0;
        }

        if ($fileLog->getCountProcessingLines() == 0 && $fileName == self::REPLACE_CSV) {
            $this->valueRepository->deleteOldData($finder);
        }

        $countProcessedRowsInCurrentFile = $fileLog->getCountProcessingRows();
        $countProcessedLinesInCurrentFile = $fileLog->getCountProcessingLines();
        for ($i = 1; $i <= $countProcessedLinesInCurrentFile; $i++) {
            fgets($fp);
        }

        //get dropdownds iDs as array
        $dropdowns = [];
        foreach ($finder->getDropdowns() as $dropdown) {
            $dropdowns[] = $dropdown->getId();
            $ranges[] = $dropdown->getRange();
        }
        $ranges[count($ranges)] = 0;

        $countDropDowns = count($dropdowns);

        $names = $this->parseFile(
            $fp,
            $countProcessedRows,
            $countProcessedRowsInCurrentFile,
            $countProcessedLinesInCurrentFile,
            $countDropDowns,
            $fileLog,
            $ranges
        );
        $namesIndex = count($names);

        $fileLog->setCountProcessingRows($countProcessedRowsInCurrentFile);
        $fileLog->setCountProcessingLines($countProcessedLinesInCurrentFile);

        if ($namesIndex) {
            $parents = $this->insertValues($names, $dropdowns, $fileLog);
            $this->createMap($parents, $names);
            $this->finderRepository->updateLinks();
        }

        if ($fileLog->getCountLines() == $countProcessedLinesInCurrentFile) {
            $fileLog->setEndedAt(date('Y-m-d H:i:s'))->archive()->delete();
        } else {
            $fileLog->setIsLocked(0)->save();
        }

        return $countProcessedRows;
    }

    /**
     * @param $fp
     * @param $countProcessedRows
     * @param $countProcessedRowsInCurrentFile
     * @param $countProcessedLinesInCurrentFile
     * @param $countDropDowns
     * @param $fileLog
     * @param $ranges
     * @return array
     */
    private function parseFile(
        $fp,
        &$countProcessedRows,
        &$countProcessedRowsInCurrentFile,
        &$countProcessedLinesInCurrentFile,
        $countDropDowns,
        $fileLog,
        $ranges
    ) {
        // convert file portion to the matrix
        // validate and normalize strings
        $names = []; // matrix h=BATCH_SIZE, w=dropNum+1;
        $namesIndex = 0;

        // need to handle ranges
        $newIndex = [];
        $tempNames = [];

        while (($line = fgetcsv($fp, \Amasty\Finder\Model\ResourceModel\Finder::MAX_LINE, ',', '"')) !== false
            && $countProcessedRows < $this->configHelper->getConfigValue('import/max_rows_per_import')) {
            $countProcessedLinesInCurrentFile++;
            $countValuesInLine = count($line);
            if ($countValuesInLine != $countDropDowns + 1 && $countValuesInLine > 1) {
                $this->importErrorModel->error(
                    $fileLog->getId(),
                    $countProcessedLinesInCurrentFile,
                    'Line #' . $countProcessedLinesInCurrentFile .
                    ' has been skipped: expected number of columns is ' . ($countDropDowns + 1)
                );
                $fileLog->error();
                continue;
            } elseif ($countValuesInLine != $countDropDowns + 1) {
                continue;
            }

            $cnt = [];
            for ($i = 0; $i < $countDropDowns + 1; $i++) {
                $line[$i] = trim($line[$i], "\r\n\t' " . '"');

                if (!$line[$i]) {
                    $this->importErrorModel->error(
                        $fileLog->getId(),
                        $countProcessedLinesInCurrentFile,
                        'Line #' . $countProcessedLinesInCurrentFile . ' contains empty columns. Possible error.'
                    );
                    $fileLog->error();
                }

                $match = [];
                if ($ranges[$i] && preg_match('/^(\d+)\-(\d+)$/', $line[$i], $match)) {
                    $cnt[$i] = abs($match[1] - $match[2]);
                }
            }

            if (!$this->validateRange($cnt, $fileLog, $countProcessedLinesInCurrentFile)) {
                continue;
            }

            ///// ***************** START old import code ************************ ////
            for ($i = 0; $i < $countDropDowns + 1; $i++) {
                $match = [];
                if ($ranges[$i] && preg_match('/^(\d+)\-(\d+)$/', $line[$i], $match)) {
                    $cnt = abs($match[1] - $match[2]);
                    if ($cnt) {
                        $startValue = min($match[1], $match[2]);
                        for ($k = 0; $k < $cnt + 1; $k++) {
                            $names[$namesIndex + $k][$i] = $startValue + $k;
                            $tempNames[$namesIndex + $k][$i] = $startValue + $k;
                            $newIndex[$i] = $namesIndex + $k;
                        }
                    } else {
                        $this->importErrorModel->error(
                            $fileLog->getId(),
                            $countProcessedLinesInCurrentFile,
                            'Line #' . $countProcessedLinesInCurrentFile .
                            ' contains the same values for the range. Possible error.'
                        );
                        $fileLog->error();
                        $names[$namesIndex][$i] = $line[$i];
                        $newIndex[$i] = $namesIndex;
                    }
                } else {
                    $names[$namesIndex][$i] = $line[$i];
                    $newIndex[$i] = $namesIndex;
                }
            }

            // multiply rows with ranges
            $multiplierRange = 1;
            $flagRange = false;

            for ($i = 0; $i < $countDropDowns + 1; $i++) {
                if ($newIndex[$i] != $namesIndex) {
                    $flagRange = true;
                    if (($newIndex[$i] - $namesIndex + 1) > 0) {
                        $multiplierRange = $multiplierRange * ($newIndex[$i] - $namesIndex + 1);
                    }
                }
            }

            if ($flagRange) {
                $currMultiply = $multiplierRange;
                for ($i = 0; $i < $countDropDowns + 1; $i++) {
                    // current multiplier for the column
                    $currMultiply = (int) $currMultiply / ($newIndex[$i] - $namesIndex + 1);
                    for ($l = 0; $l < $multiplierRange; $l++) {
                        $index = $namesIndex + (int)($l % ($currMultiply * ($newIndex[$i] - $namesIndex + 1))) /
                            $currMultiply;
                        if (isset($tempNames[$index][$i])) {
                            $names[$namesIndex + $l][$i] = $tempNames[$index][$i];
                        } else {
                            $names[$namesIndex + $l][$i] = $names[$index][$i];
                        }
                    }
                }
            }
            $namesIndex = $namesIndex + $multiplierRange;
            $tempNames = [];

            $countProcessedRowsInCurrentFile += $multiplierRange;
            $countProcessedRows += $multiplierRange;
            ///// *****************  END old import code ************************ ////
        }

        return $names;
    }

    /**
     * @param $names
     * @param $dropdowns
     * @return array
     */
    private function insertValues($names, $dropdowns, $fileLog)
    {
        $namesIndex = count($names);
        $countDropDowns = count($dropdowns);
        // like names, but
        // a) contains real IDs from db
        // b) has additional first column=0 as artificial parent_id for the frist dropdown
        // c) has no SKU
        // d) initilized by 0
        $parents = array_fill(0, $namesIndex, array_fill(0, $countDropDowns, 0));

        for ($j = 0; $j < $countDropDowns; ++$j) { // columns
            $insertedData = [];
            for ($i = 0; $i < $namesIndex; ++$i) { //rows
                $names[$i][$j] = $this->cleanName($names[$i][$j]);
                $key = $parents[$i][$j] . '-' . $names[$i][$j];
                if (!isset($insertedData[$key])) {
                    $insertedData[$key] = $parents[$i][$j];
                    $this->valueRepository->saveValue($parents[$i][$j], $dropdowns[$j], $names[$i][$j]);
                }
            }

            // now we need to select just inserted data to get IDs
            // we can create long where statement or select a bit more data that we actually need.
            // we are implementing the second approach
            $affectedParents = array_keys(array_flip($insertedData));

            $map = $this->valueRepository->getByParentAndDropdownIds($affectedParents, $dropdowns[$j])->getItems();
            $resultMap = [];

            foreach ($map as $item) {
                $resultMap[$item->getParentId() . '-' . $item->getName()] = $item->getValueId();
            }

            for ($i = 0; $i < $namesIndex; ++$i) { //rows
                $key = $parents[$i][$j] . '-' . $names[$i][$j];
                if (!isset($resultMap[$key])) {
                    $this->importErrorModel->error($fileLog->getId(), 0, 'Wrong SKU is "' . $names[$i][$j] . '"');
                    continue;
                }
                $parents[$i][$j + 1] = $resultMap[$key];

            }
        } //end columns

        return $parents;
    }

    /**
     * @param $name
     * @return string
     */
    private function cleanName($name)
    {
        $name = trim($name, '﻿'); //symbol with ascii code 92
        $name = trim($name);
        return $name;
    }

    /**
     * @param $listValues
     * @param $listSkus
     */
    private function createMap($listValues, $listSkus)
    {
        $insertedData = [];
        $namesIndex = count($listValues);
        for ($i = 0; $i < $namesIndex; ++$i) {
            $valueId = array_pop($listValues[$i]);
            $skus = explode(',', array_pop($listSkus[$i]));
            foreach ($skus as $sku) {
                $key = $valueId . '-' . $sku;
                if (!isset($insertedData[$key])) {
                    $insertedData[$key] = 1;
                    $this->mapRepository->saveMap($valueId, $sku);
                }
            }
        }
    }

    public function runAll()
    {
        $dir = $this->helper->getFtpImportDir();

        $finderIds = [];
        if ($dirHandle = opendir($dir)) {
            while (false !== ($childrenDir = readdir($dirHandle))) {
                if (!is_dir($dir . $childrenDir) || (int) $childrenDir != $childrenDir) {
                    continue;
                }
                $finderIds[] = $childrenDir;
            }
            closedir($dirHandle);
        }

        if (!empty($finderIds)) {
            $collectionFinder = $this->finderRepository->getFindersByIds($finderIds);

            foreach ($collectionFinder as $finder) {
                $this->loadNewFilesFromFtp($finder->getId());
            }
        }

        $collection = $this->importLogRepository->getNotLockedFiles();
        $countProcessedRows = 0;
        foreach ($collection as $fileLog) {
            $this->runFile($fileLog, $countProcessedRows);
            if ($countProcessedRows >= $this->configHelper->getConfigValue('import/max_rows_per_import')) {
                break;
            }
        }
    }

    /**
     * @param $fileName
     * @param $finderId
     * @return \Magento\Framework\DataObject[]
     */
    public function getLog($fileName, $finderId)
    {
        return $this->importLogRepository->getByNameAndFinder($fileName, $finderId);
    }

    /**
     * @param $finderId
     */
    public function loadNewFilesFromFtp($finderId)
    {
        $dir = $this->helper->getFtpImportDir() . $finderId . "/";
        if (!is_dir($dir)) {
            return;
        }
        $hasDeleteAllFiles = false;
        $dirHandle = opendir($dir);
        while (false !== ($file = readdir($dirHandle))) {
            if (is_file($dir . $file) && $file != '..' && $file != '.') {
                $this->importLogRepository->addUniqueFile($file, $finderId);
                if ($file == self::REPLACE_CSV) {
                    $hasDeleteAllFiles = true;
                }
            }
        }
        closedir($dirHandle);

        if ($hasDeleteAllFiles) {
            $this->importLogRepository->deleteByIdWithoutReplaceFile($finderId);
        }
    }

    /**
     * @param $fileField
     * @param $finderId
     * @param null $fileName
     * @return null|string
     * @throws ValidatorException
     */
    public function upload($fileField, $finderId, $fileName = null)
    {
        $dir = $this->helper->getFtpImportDir() . $finderId . "/";

        $uploader = $this->uploaderFactory->create(['fileId' => $fileField]);
        $uploader->setAllowedExtensions(['csv']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        if ($this->importLogRepository->hasIssetReplaceFile($finderId)) {
            throw new ValidatorException(__('Upload is impossible, there is a file replace.csv'));
        }

        if ($fileName !== null && is_file($dir . $fileName)) {
            throw new ValidatorException(__('The file with the same name already exists! ' . $fileName));
        }

        $result = $uploader->save($dir, $fileName);

        if (!$result) {
            throw new ValidatorException(__('Error occurred save file'));
        }

        $fileName = $uploader->getUploadedFileName();
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($dir . $fileName);
            if ($mimeType != 'text/plain') {
                @unlink($dir . $fileName);
                throw new ValidatorException(__('Incorrect file type. CSV needed'));
            }
        }
        $this->loadNewFilesFromFtp($finderId);
        return $fileName;
    }

    /**
     * @param $fileHandle
     * @return int
     */
    public function countLines($fileHandle)
    {
        $i = 0;
        while (fgets($fileHandle) !== false) {
            $i++;
        }

        rewind($fileHandle);

        return $i;
    }

    /**
     * @param $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        return $this->importLogRepository->getTable($tableName);
    }

    /**
     * @param $finderId
     */
    public function afterDeleteFinder($finderId)
    {
        $this->importLogRepository->deleteByFinderId($finderId);
        $this->importHistoryLogRepository->deleteByFinderId($finderId);
    }
}
