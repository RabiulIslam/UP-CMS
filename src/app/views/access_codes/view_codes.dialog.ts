import { Component, OnInit, ElementRef, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog, MatDialogRef } from '@angular/material';

import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog } from '../../lib';
import { appConfig } from '../../../config';
import { ExportCSVComponent } from '../../lib/export_csv.component';
import { AppLoaderService } from '../../lib/app-loader/app-loader.service';


@Component({
	selector: 'app-view-codes',
	templateUrl: './view_codes.dialog.html'
})
export class ViewCodesComponent extends ExportCSVComponent implements OnInit, AfterViewInit
{
	
	sub: Subscription;
	Deal: any;
    mAccessCodes: any[];
    currentPage: number;
    index: number;
    accesscodesCount: any;
    pages: any;
    totalPages: any;
	perPage: any;

	constructor(private elRef: ElementRef, protected router: Router,
        protected mainApiService: MainService,
        protected paginationService: PaginationService, 
		// protected mloaderService: BaseLoaderService, 
		protected appLoaderService: AppLoaderService,
        protected dialog: MatDialog, protected dialogRef: MatDialogRef<ViewCodesComponent>)	
	{
		super(mainApiService, appLoaderService, dialog);
        this.Deal = null;
		this.mAccessCodes = [];
		this.perPage = 20;
		this.method = 'getMultipleAccessCode';
	}

	ngOnInit() 
	{
		this.germAccessCodesList(1);
		this.parent_id = this.Deal.id;
		this.isNeededDate = false;
    }

    ngAfterViewInit()
    {
        this.elRef.nativeElement.parentElement.classList.add("mat-dialog-changes-1");
    }

    germAccessCodesList(index, isLoaderHidden?: boolean): void
	{
		// if(isLoaderHidden)
		// {
		// 	this.mloaderService.setLoading(false);
		// }
		// else
		// {
		// 	this.mloaderService.setLoading(true);
        // }
        
        let url = 'getMultipleAccessCode?index=' + index + '&index2=' + this.perPage + '&id=' + this.Deal.id;
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				let mAccessCodes : any = result.data.accesscodes;
				this.accesscodesCount = result.data.accesscodesCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.accesscodesCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				// this.mloaderService.setLoading(false);

				mAccessCodes.forEach(element => {
					if(element.status == 1)
					{
						element['slide'] = true;
					}
					else if(element.status == 0)
					{
						element['slide'] = false;
					}
				});
				// console.log('dsdsd')
				this.mAccessCodes = mAccessCodes;
			}
			else
			{
				this.mAccessCodes = [];
				this.accesscodesCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.accesscodesCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				// this.mloaderService.setLoading(false);
			}
		});
	}


	setPage(pageDate: any) 
	{
		this.currentPage = pageDate.page;
		this.perPage = pageDate.perPage;
		this.index = this.currentPage;
		this.germAccessCodesList(pageDate.page);
	}
    
    onChangemAccessCode(accessCode): void 
	{
		let status: any;
		if(accessCode.slide)
		{
			status = 1;
		}
		else
		{
			status = 0;
		}
		let merchantData = {
			id: accessCode.id,
			status: status
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Change AccessCode';
		cm.message = 'Are you sure to Update AccessCode';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName = appConfig.base_url_slug + 'updateAccessCode';
		cm.dataToSubmit = merchantData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			console.log(result);
			if(result)
			{
				this.germAccessCodesList(this.currentPage, true);
			}
			else
			{
				accessCode.slide = !accessCode.slide;
			}
		})
	}
}
