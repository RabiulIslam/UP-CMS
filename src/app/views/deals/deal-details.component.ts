import { Component, OnInit, ElementRef, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog, MatDialogRef } from '@angular/material';


import { MainService, BaseLoaderService } from '../../services';
import { AlertDialog } from '../../lib';
import { appConfig } from '../../../config';


@Component({
	selector: 'app-deal-details',
	templateUrl: './deal-details.component.html'
})
export class DealDetailsComponent implements OnInit, AfterViewInit
{
	
	sub: Subscription;
	Deal: any;
    Offers: any;
    status: boolean;

	constructor(private elRef: ElementRef, protected router: Router,
		protected mainApiService: MainService,
		protected loaderService: BaseLoaderService, protected dialog: MatDialog, protected dialogRef: MatDialogRef<DealDetailsComponent>)	
	{
        this.Deal = null;
        this.Offers = [];
        this.status = false;
	}

	ngOnInit() 
	{
        this.getOffers();
        if(this.Deal.active == 1)
        {
            this.status = true;
        }
        else if(this.Deal.active == 0)
        {
            this.status = false;
        }
    }

    ngAfterViewInit()
    {
        this.elRef.nativeElement.parentElement.classList.add("mat-dialog-changes-1");
    }

    
    onEditDeal(): void 
	{
		localStorage.setItem('Deal', JSON.stringify(this.Deal));
        this.router.navigateByUrl('main/deals/' + this.Deal.id);
        this.dialogRef.close();
		event.stopPropagation();
    }
    
    onChangeStatus(): void 
	{
		let active: any;
		if(this.status)
		{
			active = 1;
		}
		else
		{
			active = 0;
		}
		let Data = {
			id: this.Deal.id,
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
			if(result)
			{
                // this.status = !this.status;
            }
            else
            {
                this.status = !this.status;
            }
		})
	}

	getOffers(): void
	{
		this.mainApiService.getList(appConfig.base_url_slug + 'getOffers?deal_id=' + this.Deal.id)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Offers = result.data.offers;
			}
			else
			{
				this.Offers = [];
			}
		});
	}
}
