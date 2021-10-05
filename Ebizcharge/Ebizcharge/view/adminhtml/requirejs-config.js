var config = {
	paths: {
        'chosen': 'Ebizcharge_Ebizcharge/js/choosen'
        },
	shim: {
        'chosen': {
            deps: ['jquery']
          }
		},
	map: {
		'*': {
            ebizcharge: 'Ebizcharge_Ebizcharge/js/form'
        }
    }	
};