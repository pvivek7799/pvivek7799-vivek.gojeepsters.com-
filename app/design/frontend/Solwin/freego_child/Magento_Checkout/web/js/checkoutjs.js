define(['jquery'], function($){
    "use strict";
    return function checkout()
    {
		if(BASE_URL=='https://largo.gojeepsters.com/'){
			$('[name="region_id"]').val(18);
			$('[name="region_id"]').trigger('change');
			$('[name="street[0]"]').val('6875 Ulmerton Rd');
			$('[name="city"]').val('Largo');
			//$('[name="telephone"]').val('+1 727 538-0086');
			$('[name="postcode"]').val('33771');
			$('[name="region_id"]').val('18');
			//$('[name="street[0]"]','[name="city"]','[name="postcode"]','[name="region_id"]').trigger('change');
		}else if(BASE_URL=='https://tampa.gojeepsters.com/'){
			$('[name="region_id"]').val(18);
			$('[name="region_id"]').trigger('change');
			$('[name="street[0]"]').val('6102 E Adamo Dr');
			$('[name="city"]').val('Tampa');
			//$('[name="telephone"]').val('+1 813 605-2244');
			$('[name="postcode"]').val('33619');
			$('[name="region_id"]').val('18');
		//$('[name="street[0]"]','[name="city"]','[name="postcode"]','[name="region_id"]').trigger('change');			
		}
        console.log(BASE_URL);
    }
});