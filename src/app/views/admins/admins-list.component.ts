import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog } from '../../lib';
import { MatDialog } from '@angular/material';
import { appConfig } from '../../../config';

@Component({
	selector: 'app-admins-list',
	templateUrl: './admins-list.component.html'
})
export class AdminsListComponent implements OnInit 
{
	search: string;
	// type: any;
	sub: Subscription;
	index:  any = 1;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage:  any = 1;
	adminsCount: any;
	Admins: any;
	searchTimer:any;
	perPage: any;
	componentSettings: any = {
		name: null,
		paggination: null,
		search: null
	};

	constructor(protected router: Router,
		protected _route: ActivatedRoute,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected dialog: MatDialog)	
	{
		this.search = '';
		this.Admins = [];
		this.perPage = 20;
	}

	ngOnInit() 
	{
		this.componentSettings = JSON.parse(localStorage.getItem('componentSettings'))
		if(this.componentSettings)
		{
			if(this.componentSettings.name != null && this.componentSettings.name == 'Admins')
			{
				this.currentPage = this.componentSettings.paggination;
				this.index = this.componentSettings.paggination;
				this.search = this.componentSettings.search;
			}
		}
		// this.sub = this._route.params.subscribe(params => {
		// 	this.type = params['type'];
		// 	if (this.type) 
		// 	{
				this.gerAdminsList(this.currentPage);
			// }
			// console.log(params);
		// });
	}

	addNew() {
		this.router.navigateByUrl('main/admins/add');
	}

	onSearchAdmin(): void
	{clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {

			this.gerAdminsList(1);

		}, 700);

	}

	gerAdminsList(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getAdmins?type=' + '&index=' + index + '&index2=' + this.perPage;

		if(this.search != '')
		{
			url = url + '&search=' + this.search;
		}

		localStorage.setItem('componentSettings', JSON.stringify(
			{
				name: 'Admins',
				paggination: this.index,
				search: this.search
			}
        ));
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Admins = result.data.admin;
				this.adminsCount = result.data.adminsCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.adminsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
			else
			{
				this.Admins = [];
				this.adminsCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.adminsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
		});
	}

	setPage(pageDate: any) 
	{
		this.currentPage = pageDate.page;
		this.perPage = pageDate.perPage;
		this.index = this.currentPage;
		this.gerAdminsList(pageDate.page);
	}


	onEditAdmin(admin): void 
	{
		localStorage.setItem('Admin', JSON.stringify(admin));
		this.router.navigateByUrl('main/admins/' + admin.id);
	}

	onDeleteAdmin(admin): void 
	{
		let adminData = {
			id: admin.id
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Delete Admin';
		cm.message = 'Are you sure to delete Admin';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName = appConfig.base_url_slug + 'deleteAdmin';
		cm.dataToSubmit = adminData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			if(result)
			{
				this.gerAdminsList(this.currentPage, true);
			}
		})
	}

}
