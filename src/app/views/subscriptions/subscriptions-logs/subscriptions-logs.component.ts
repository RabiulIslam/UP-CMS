import { Component, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';
import { ActivatedRoute, Router } from '@angular/router';
import { MainService, BaseLoaderService, PaginationService } from '../../../services';
import { MatDialog } from '@angular/material';
import { appConfig } from '../../../../config';

@Component({
  selector: 'app-subscriptions-logs',
  templateUrl: './subscriptions-logs.component.html',
  styleUrls: ['./subscriptions-logs.component.css']
})
export class SubscriptionLogsComponent implements OnInit 
{
	search: string;
	sub: Subscription;
	index: any;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage: any;
	subscriptionsCount: any;
	SubscriptionLogs: any;
	id: any;
	perPage: number;
	// operator: any;

	constructor(protected router: Router,
		protected _route: ActivatedRoute,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected dialog: MatDialog)	
	{
		this.search = '';
		this.SubscriptionLogs = [];
		this.perPage = 20;
		// this.operator = 'All';
	}

	ngOnInit() 
	{
		// this.gerSubscriptionLogs(1);

		this.sub = this._route.params.subscribe(params => {
			this.id = params['id'];
			if (this.id) 
			{
				this.gerSubscriptionLogs(1);
			}
			console.log(params);
		});
	}

	onSearchSubscription(): void
	{
		this.gerSubscriptionLogs(1);
	}

	gerSubscriptionLogs(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getSubscriptionLogs?index=' + index + '&index2=' + this.perPage;

		if(this.search != '')
		{
			url = url + '&search=' + this.search;
		}

		if(this.id != 'All')
		{
			url = url + '&user_id=' + this.id;
		}
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.SubscriptionLogs = result.data.subscriptions;
				this.subscriptionsCount = result.data.subscriptionsCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.subscriptionsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
			else
			{
				this.SubscriptionLogs = [];
				this.subscriptionsCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.subscriptionsCount, index, this.perPage);
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
		this.gerSubscriptionLogs(pageDate.page);
	}


	onLocationBack(): void
	{
		window.history.back();
	}

}
