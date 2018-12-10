import {Component, OnInit, ViewChild, Input, Output, EventEmitter, OnChanges, SimpleChanges} from '@angular/core';
import {Subscription} from 'rxjs';
import {Router, ActivatedRoute} from '@angular/router';
import {MatDialog} from '@angular/material';
import {FormBuilder, FormGroup, Validators, FormControl} from '@angular/forms';


import {AlertDialog, GetLocationDialog} from '../../lib';
import {MainService} from '../../services';
import {appConfig} from '../../../config';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { ImportCSVComponent } from '../../lib/import_csv.component';

declare var $: any;

@Component({
    selector: 'app-outlets-form',
    templateUrl: './outlets-form.component.html'
})
export class OutletsFormComponent extends ImportCSVComponent implements OnInit, OnChanges 
{    
    id: any = 'add';
    // type: any;
    sub: Subscription;
    Form: FormGroup;
    // isLoading: boolean;
    isEditing: boolean;
    outlet: any;

    startTiming: any;
    endTiming: any;
    errorMsg: string;
    errorMsglogo: string;
    errorMsgimage: string;
    Merchants: any[];
    Categories: any[];
    file_url: any = appConfig.file_url;
    // csvFile: any;
    // outletsJSON: string;
    showError: boolean;
    status: boolean;
    Offers: any;

    filteredOptions: Observable<string[]>;
    // errorCounter: number;
    // errorMessageForCSV: string;
    // @ViewChild('selectTo') selectTo: any;

    @Input() is_heading_shown: boolean = true;
    @Input() is_button_shown: boolean = true;
    @Input() heading_label: string = 'OUTLET';
    @Input() is_child: boolean = false;
    @Input() parent_key: number = 0;
    @Output() onFormChanges: EventEmitter<any> = new EventEmitter<any>();
    @Output() onOutletSuccess: EventEmitter<any> = new EventEmitter<any>();
    @Input() isMultiple: boolean = false;
    isGenderDisabled: boolean;

    constructor(protected router: Router,
                protected _route: ActivatedRoute,
                protected mainApiService: MainService,
                protected formbuilder: FormBuilder, protected dialog: MatDialog) 
    {
        super(mainApiService, dialog);
        this.Form = this.formbuilder.group({
            name: [null, [Validators.required, Validators.maxLength(50)]],
            phone: [null, [Validators.required, Validators.maxLength(20), Validators.minLength(8)]],
            address: [null, [Validators.required]],
            description: [null, [Validators.required]],
            // neighborhood: [null, [Validators.required, Validators.maxLength(50)]],
            pin: [null, [Validators.required, Validators.maxLength(4)]],
            timings: [null, [Validators.required, Validators.maxLength(100)]],

            latlng: [null, [Validators.required]],
            SKU: [null],
            emails: [null, [Validators.required]],
            logo: [null],
            logo_name: [null, [Validators.required]],
            image: [null],
            image_name: [null, [Validators.required]],

            type: [null],
            special: [null, [Validators.required, Validators.maxLength(50)]],
            search_tags: [null, [Validators.required, Validators.maxLength(300)]],
            merchant_id: [null, [Validators.required]],
            category_ids: [null, [Validators.required]],
            merchantObject: ['', [Validators.required, Validators.maxLength(50)]],
        });

        this.isLoading = false;
        this.isEditing = false;
        // this.outletsJSON = '';
        this.Offers = [];
        this.Merchants = [];
        this.errorMessageForCSV = 'Invalid CSV File. <br>';
        this.isGenderDisabled = true;
        this.methodOfCsv = 'addOutlets';

        this.Form.valueChanges.subscribe(response => 
        {
            if(response.category_ids != null)
            {
                if(response.category_ids.includes('64'))
                {
                    this.isGenderDisabled = false;
                }
                else
                {
                    this.isGenderDisabled = true;
                }
            }

            if(this.Form.valid)
            {
                this.onFormChanges.emit(this.Form);
            }
            else
            {
                this.onFormChanges.emit(false);
            }
        })
    }

    equals(objOne, objTwo) 
    {
        if (typeof objOne !== 'undefined' && typeof objTwo !== 'undefined') 
        {
            return objOne.id === objTwo.id;
        }
    }

    ngOnChanges(changes: SimpleChanges): void 
    {
        if(this.parent_key != void 0 && this.Form.valid)
        {
            this.Form.get('merchant_id').setValue(this.parent_key); 
            this.Form.get('merchantObject').setValue(this.parent_key); 
            this.doSubmit();
        }
        
    }

    ngOnInit() 
    {
        if(this.is_child == false)
        {
            this.Form.get('merchantObject').valueChanges.subscribe(response => 
                {
                    if(response == null)
                    {
                        return
                    }
                    if(typeof response !== 'object')
                    {
                        this.Form.get('merchantObject').setErrors(Validators.requiredTrue);
                    }
                    else
                    {
                        this.Form.get('merchant_id').setValue(response.id); 
                    }
                })
    
            this.filteredOptions = this.Form.get('merchantObject').valueChanges.pipe(
                startWith<any>(''),
                map(value => typeof value === 'string' ? value : value.name),
                map(name => name ? this._filter(name) : this.Merchants.slice())
            );
            
    
            this.sub = this._route.params.subscribe(params => {
                this.id = params['id'];
                if (this.id != 'add') 
                {
                    this.Form.addControl('id', new FormControl(this.id));
                    this.outlet = JSON.parse(localStorage.getItem('Outlet'));
                    this.Form.patchValue(this.outlet);
                    let cart: any;
                    if (this.outlet.category_ids) {
                        cart = this.outlet.category_ids.split(',');
                    }
                    this.Form.get('category_ids').setValue(cart);
                    this.isEditing = true;

                    this.Form.get('merchantObject').setValue({
                        id: this.outlet.merchant_id, name: this.outlet.merchant_name
                    })

                    this.Form.get('latlng').setValue(this.outlet.latitude + ',' + this.outlet.longitude);
                   
    
                }
                else {
                    this.isEditing = false;
                    this.Form.reset();
                }
            });
            this.gerMerchantsList();
        }
        else
        {
            if(!this.isMultiple)
            {
                this.Form.get('merchant_id').setValidators(null); 
                this.Form.get('merchantObject').setValidators(null); 
            }
            else
            {
                this.Form.get('merchant_id').setValue(this.parent_key); 
                this.Form.get('merchantObject').setValue(this.parent_key); 
            }
        }

        this.getCategoriesList();
        this.getOffers();
    }

    onTagAdd(event): void
    {
        this.Form.get('emails').setValue(event);
    }

    getValue(name) 
    {
        return this.Form.get(name);
    }

    getImage(item): any 
    {
        if (this.id != 'add') 
        {
            return this.file_url + this.outlet[item];
        }
        else 
        {
            return '';
        }
    }

    onLocationBack(): void {
        window.history.back();
    }

    doSubmit(): void {
        this.isLoading = true;
        let method = '';
        let formData = new FormData();
        if (this.id == 'add') {
            method = 'addOutlet';
        }
        else {
            formData.append('id', this.Form.get('id').value);
            method = 'updateOutlet';
        }

        formData.append('name', this.Form.get('name').value);
        formData.append('phone', this.Form.get('phone').value);
        formData.append('address', this.Form.get('address').value);
        formData.append('description', this.Form.get('description').value);
        // formData.append('neighborhood', this.Form.get('neighborhood').value);
        formData.append('pin', this.Form.get('pin').value);
        formData.append('timings', this.Form.get('timings').value);
        
        formData.append('logo', this.Form.get('logo').value);
        formData.append('SKU', this.Form.get('SKU').value);
        formData.append('image', this.Form.get('image').value);
        formData.append('logo_name', this.Form.get('logo_name').value);
        formData.append('image_name', this.Form.get('image_name').value);

        formData.append('type', this.Form.get('type').value);
        formData.append('special', this.Form.get('special').value);
        formData.append('search_tags', this.Form.get('search_tags').value);
        formData.append('merchant_id', this.Form.get('merchant_id').value);
        formData.append('emails', this.Form.get('emails').value);

        if(this.Form.get('latlng').value != null || this.Form.get('latlng').value != '')
        {
            let latlng = this.Form.get('latlng').value.split(',')
            formData.append('latitude', latlng[0]);
            formData.append('longitude', latlng[1]);
        }

        let cat_ids = this.Form.get('category_ids').value.join();
        formData.append('category_ids', cat_ids);

        this.mainApiService.postData(appConfig.base_url_slug + method, formData).then(response => {
                if (response.status == 200 || response.status === 201) {
                    
                    if(this.is_child == false)
                    {
                        this.router.navigateByUrl('/main/outlets');
                    }
                    else
                    {
                        if(this.isMultiple)
                        {
                            this.onOutletSuccess.emit(this.Form.value);
                        }
                        else
                        {
                            window.history.back();
                        }
                    }
                    this.isLoading = false;
                }
                else {
                    this.isLoading = false;
                    let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
                    let cm = dialogRef.componentInstance;
                    cm.heading = 'Error';
                    cm.message = response.error.message;
                    cm.cancelButtonText = 'Ok';
                    cm.type = 'error';
                }
            },
            Error => {
                // console.log(Error)
                this.isLoading = false;
                let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
                let cm = dialogRef.componentInstance;
                cm.heading = 'Error';
                cm.message = "Internal Server Error.";
                cm.cancelButtonText = 'Ok';
                cm.type = 'error';
            })
    }

    getCategoriesList(): void {
        this.mainApiService.getList(appConfig.base_url_slug + 'getCategories')
            .then(result => {
                if (result.status === 200 && result.data) {
                    this.Categories = result.data.categories;
                }
                else {
                    this.Categories = [];
                }
            });
    }

    gerMerchantsList(): void 
	{
		let url = 'getAllMerchants';

		this.mainApiService.getList(appConfig.base_url_slug + url).then(result => 
		{
			if (result.status === 200 && result.data) 
			{
				// this.Outlets = result.data;

				result.data.forEach(element => {
					if(element.id != null && element.name != null)
					{
						this.Merchants.push(element);
					}
				});
				
				this.filteredOptions = this.Form.get('merchantObject').valueChanges.pipe(
					startWith<any>(''),
					map(value => typeof value === 'string' ? value : value.name),
					map(name => name ? this._filter(name) : this.Merchants.slice())
				);
                }
			else 
			{
				this.Merchants = [];
				this.filteredOptions = this.Form.get('merchantObject').valueChanges.pipe(
					startWith<any>(''),
					map(value => typeof value === 'string' ? value : value.name),
					map(name => name ? this._filter(name) : this.Merchants.slice())
				);
                }
            });
    }
    
    displayFn(user?: any): string | undefined 
	{
		return user ? user.name : undefined;
    }
    
    private _filter(name: string): any[] 
	{
		const filterValue = name.toLowerCase();
		return this.Merchants.filter(option => option.name.toLowerCase().indexOf(filterValue) === 0);
	}

    onFileSelect(event) 
    {
        if (event.controlName == 'logo') 
        {
            if (event.valid) 
            {
                this.Form.get(event.controlName).patchValue(event.file);
                this.errorMsglogo = '';
            } 
            else 
            {
                this.errorMsglogo = 'Please select square logo'
            }

        }
        if (event.controlName == 'image') 
        {
            if (event.valid) 
            {
                this.Form.get(event.controlName).patchValue(event.file);
                this.errorMsgimage = '';
            }
            else 
            {
                this.errorMsgimage = 'Please select square image'
            }
        }

    }

    onGetLocation(): void {
        let dialogRef = this.dialog.open(GetLocationDialog, {autoFocus: false});
        if (this.Form.get('latitude').value != null && this.Form.get('longitude').value != null) {
            dialogRef.componentInstance.initialLocation = this.Form.get('latitude').value + ',' + this.Form.get('longitude').value;
        }

        dialogRef.afterClosed().subscribe(result => {
            // console.log(result);
            if (result) {
                this.Form.get('latitude').setValue(result.lat);
                this.Form.get('longitude').setValue(result.lng);
                this.Form.get('address').setValue(result.formatted_address);
            }
        })
    }

    getOffers(): void {
        if (this.isEditing == false) {
            return;
        }
        this.mainApiService.getList(appConfig.base_url_slug + 'getOffers?outlet_id=' + this.outlet.id)
            .then(result => {
                if (result.status === 200 && result.data) {
                    // this.Offers = result.data.offers;

                    let Offers: any = result.data.offers;

                    Offers.forEach(element => {
                        if (element.active == 1) {
                            element['slide'] = true;
                        }
                        else if (element.active == 0) {
                            element['slide'] = false;
                        }
                    });
                    // console.log('dsdsd')
                    this.Offers = Offers;
                }
                else {
                    this.Offers = [];
                }
            });
    }

    onDealNameClick(deal): void {
        localStorage.setItem('Deal', JSON.stringify(deal));
        this.router.navigateByUrl('main/deals/' + deal.id);
    }

    onChangeDealStatus(deal): void {
        let active: any;
        if (deal.slide) {
            active = 1;
        }
        else {
            active = 0;
        }
        let Data = {
            id: deal.id,
            active: active
        };

        let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
        let cm = dialogRef.componentInstance;
        cm.heading = 'Change Deal';
        cm.message = 'Are you sure to Update Deal';
        cm.submitButtonText = 'Yes';
        cm.cancelButtonText = 'No';
        cm.type = 'ask';
        cm.methodName = appConfig.base_url_slug + 'ADOffer';
        cm.dataToSubmit = Data;
        cm.showLoading = true;

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                this.getOffers();
            }
            else {
                this.status = !this.status;
            }
        })
    }

    afterSelectionCsv(result, headersObj, objTemp): void
    {
        console.log('kslklsakl')
        for (let key in headersObj) 
		{
			if(!headersObj.hasOwnProperty('merchant_id') && !objTemp.hasOwnProperty('merchant_id'))
			{
				objTemp['merchant_id'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>merchant_id</b> is missing,<br> ';
				this.errorCounter++;
			} 
			else if(!headersObj.hasOwnProperty('category_ids') && !objTemp.hasOwnProperty('category_ids'))
			{
				objTemp['category_ids'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>category_ids</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('logo_name') && !objTemp.hasOwnProperty('logo_name'))
			{
				objTemp['logo_name'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>logo_name</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('image_name') && !objTemp.hasOwnProperty('image_name'))
			{
				objTemp['image_name'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>image_name</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('name') && !objTemp.hasOwnProperty('name'))
			{
				objTemp['name'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>name</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('phone') && !objTemp.hasOwnProperty('phone'))
			{
				objTemp['phone'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>phone</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('address') && !objTemp.hasOwnProperty('address'))
			{
				objTemp['address'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>address</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('description') && !objTemp.hasOwnProperty('description'))
			{
				objTemp['description'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>description</b> is missing,<br> ';
				this.errorCounter++;
			}
			else if(!headersObj.hasOwnProperty('timings') && !objTemp.hasOwnProperty('timings'))
			{
				objTemp['timings'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>timings</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('latitude') && !objTemp.hasOwnProperty('latitude'))
			{
				objTemp['latitude'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>latitude</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('longitude') && !objTemp.hasOwnProperty('longitude'))
			{
				objTemp['longitude'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>longitude</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('pin') && !objTemp.hasOwnProperty('pin'))
			{
				objTemp['pin'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>pin</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('search_tags') && !objTemp.hasOwnProperty('search_tags'))
			{
				objTemp['search_tags'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>search_tags</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('special') && !objTemp.hasOwnProperty('special'))
			{
				objTemp['special'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>special</b> is missing,<br> ';
				this.errorCounter++;
            }
            else if(!headersObj.hasOwnProperty('emails') && !objTemp.hasOwnProperty('emails'))
			{
				objTemp['emails'] = null;
				this.errorMessageForCSV = this.errorMessageForCSV + '<b>emails</b> is missing,<br> ';
				this.errorCounter++;
            }
            
            if(headersObj.hasOwnProperty('category_ids'))
			{
				if(!headersObj.hasOwnProperty('type') && !objTemp.hasOwnProperty('type'))
                {
                    objTemp['type'] = null;
                    this.errorMessageForCSV = this.errorMessageForCSV + '<b>type</b> is missing,<br> ';
                    this.errorCounter++;
                }
			}
        }

		if(this.errorCounter == 0)
		{
			result.forEach((element, index) => 
			{
				if(element['merchant_id'] == null || element['merchant_id'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>merchant_id</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['logo_name'] == null || element['logo_name'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>logo_name</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['image_name'] == null || element['image_name'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>image_name</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['name'] == null || element['name'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>name</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['phone'] == null || element['phone'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>phone</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['address'] == null || element['address'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>address</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['description'] == null || element['description'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>description</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
				}
				if(element['timings'] == null || element['timings'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>timings</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                if(element['latitude'] == null || element['latitude'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>latitude</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                if(element['longitude'] == null || element['longitude'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>longitude</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                if(element['special'] == null || element['special'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>special</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                
                if(element['pin'] == null || element['pin'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>pin</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                if(element['search_tags'] == null || element['search_tags'] == '')
				{
					this.errorMessageForCSV = this.errorMessageForCSV + '<b>search_tags</b> is empty on line number ' + (index + 1) + ',<br> ';
					this.errorCounter++;
                }
                if(element['emails'] != null || element['emails'] != '')
				{
					element['emails'] = element['emails'].split(';').join(',');
                }
                if(element['category_ids'] != null || element['category_ids'] != '')
				{
                    let category_ids = element['category_ids'].split(';');

                    if(category_ids.includes('64'))
                    {
                        if(element['type'] == null || element['type'] == '')
                        {
                        	this.errorMessageForCSV = this.errorMessageForCSV + '<b>type</b> is empty on line number ' + (index + 1) + ',<br> ';
                        	this.errorCounter++;
                        }        
                    }
                    element['category_ids'] = element['category_ids'].split(';').join(',');
                }
                
            });
        }
        this.afterJSON = JSON.stringify(result);
    }

    onUploadCSV(): void
    {
        this.JsonToServer = {outlets: this.afterJSON};
        super.onUploadCSV();
    }

    afterSuccess(): void
    {
        this.router.navigateByUrl('/main/outlets');
        this.isLoading = false;
    }
}
