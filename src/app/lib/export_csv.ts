import { OnInit, Component } from "@angular/core";
import { MatDialogRef } from "@angular/material/dialog";

import { MainService } from "../services/main.service";
import * as moment from 'moment';
import { FormBuilder, Validators, FormGroup } from "@angular/forms";

@Component({
	selector: 'export-csv',
	templateUrl: './export_csv.html'
})
export class ExportCSVDialog implements OnInit 
{
	isLoading: boolean;
	heading: string = '';
	message: string = '';
	showLoading: boolean;
    Form: FormGroup;
    selectedDateRange: any;

	constructor(protected mainApiService: MainService,protected formbuilder: FormBuilder, protected dialogRef: MatDialogRef<ExportCSVDialog>) 
	{
		this.isLoading = false;
        this.showLoading  = false;
        
        this.Form = this.formbuilder.group({
            dateRange: [null, [Validators.required]],
            start_date: [null, [Validators.required]],
            end_date: [null, [Validators.required]],
        });
	}

	ngOnInit() 
	{
    }
    
    onSelectDateRange(): void 
    {
        let abc1 = moment(this.selectedDateRange[0]).format('YYYY-MM-DD');
        this.Form.get('start_date').setValue(abc1);

        let abc2 = moment(this.selectedDateRange[1]).format('YYYY-MM-DD');
        this.Form.get('end_date').setValue(abc2);
    }

    getValue(name) 
    {
        return this.Form.get(name);
    }

	onCancelClick(): void
	{
		this.dialogRef.close(false);
	}

	onSubmitClick(): void
	{
        console.log(this.Form.value);
        let dict = {
            start_date: this.Form.get('start_date').value, 
            end_date: this.Form.get('end_date').value
        };
        this.dialogRef.close(dict);
	}
}
