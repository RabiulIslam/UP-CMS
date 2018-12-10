
import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { MainService } from '../../services/main.service';
import { appConfig } from '../../../config';


declare var $: any;

export class SideMenuItems {
	routerLink?: string;
	image?: string;
	label?: string;
	is_parent?: boolean;
	is_hidden?: boolean;
	children?: SideMenuItems[];
	opened?: boolean;
}

@Component({
	selector: 'template-main-app',
	templateUrl: 'template-main-app.component.html',
	styleUrls: ['template-main-app.component.scss'],
})
export class TemplateMainApp implements OnInit, OnDestroy {

	timer: any;
	Notifications: any;
	adminClick: boolean;
	// notificationClick:any;
	public scrollbarOptions = { axis: 'yx', theme: 'minimal' };

	menus: SideMenuItems[];

	constructor(protected mainApiService: MainService, private router: Router) 
	{
		this.adminClick = false;

		this.menus = [
			// { routerLink: '/main/dashboard', image: 'dashboard@2x', label: 'Dashboard' },
			// {
			// 	image: 'Admin@2x',
			// 	label: 'Admins',
			// 	is_parent: true,
			// 	opened: false,
			// 	children: [
			// 		{ routerLink: '/main/admins/0', image: 'Admin@2x', label: 'Super Admin' },
			// 		{ routerLink: '/main/admins/1', image: 'Admin@2x', label: 'Branch Manager'	},
			// 		{ routerLink: '/main/admins/2', image: 'Driver@2x', label: 'Drivers' },
			// 	]
			// },
			// { routerLink: '/main/customers', image: 'customers@2x', label: 'Customers' },
			// { routerLink: '/main/branches', image: 'branches@2x', label: 'Branches' },
			// { routerLink: '/main/menu_items', image: 'Menu@2x', label: 'Menu Items' },
			// // { routerLink: '/main/offers', image: 'Offers@2x', label: 'Offers' },
			// { routerLink: '/main/orders', image: 'Orders@2x', label: 'Orders' },
			// { routerLink: '/main/promo_code', image: 'Promo@2x', label: 'Promo Code' },
			// { routerLink: '/main/notifications', image: 'Notifications@2x', label: 'Notifications' },
			// { routerLink: '/main/settings', image: 'Settings@2x', label: 'Settings' }
		];

		this.Notifications = {
			ordersCount: 0,
			maxId: 0
		}
	}
	ngOnInit() 
	{
		let UrbanpointAdmin = JSON.parse(localStorage.getItem('UrbanpointAdmin'));

		// if(UrbanpointAdmin.type == 0)
		{
			this.menus = [
				
				// {
				// 	image: 'Admin@2x',
				// 	label: 'Admins',
				// 	is_parent: true,
				// 	opened: false,
				// 	children: [
				// 		{ routerLink: '/main/admins/0', image: 'Admin@2x', label: 'Super Admin' },
				// 		{ routerLink: '/main/admins/1', image: 'Admin@2x', label: 'Branch Manager'},
				// 		{ routerLink: '/main/admins/2', image: 'Driver@2x', label: 'Drivers' },
				// 	]
				// },
				{ routerLink: '/main/admins', image: 'dashboard', label: 'ADMINS' },
				{ routerLink: '/main/merchants', image: 'merchants', label: 'ORGANIZATIONS' },
				{ routerLink: '/main/outlets', image: 'outlets', label: 'OUTLETS' },
				{ routerLink: '/main/deals', image: 'deals', label: 'OFFERS' },
				// { routerLink: '/main/customers', image: 'customers', label: 'CUSTOMERS' },
				{
					image: 'customers', 
					label: 'CUSTOMERS',
					is_parent: true,
					opened: false,
					children: [
						{ routerLink: '/main/customers/registered', image: 'defaults', label: 'REGISTERED' },
						{ routerLink: '/main/customers/non_registered', image: 'defaults', label: 'NON REGISTERED' },
					]
				},
				{ routerLink: '/main/orders/All', image: 'orders', label: 'ORDERS' },
				{ routerLink: '/main/subscriptions/All', image: 'subscribe-orange', label: 'SUBSCRIPTIONS' },
				{ routerLink: '/main/notifications', image: 'notifications', label: 'NOTIFICATIONS' },
				{ routerLink: '/main/access_codes', image: 'access_codes', label: 'ACCESS CODES' },
				// {
				// 	image: 'access_codes', 
				// 	label: 'ACCESS CODES',
				// 	is_parent: true,
				// 	opened: false,
				// 	children: [
				// 		{ routerLink: '/main/access_codes/list/first_app', image: 'access_codes', label: 'ACCESS CODES APP 1' },
				// 		{ routerLink: '/main/access_codes/list/second_app', image: 'access_codes', label: 'ACCESS CODES APP 2' }
				// 	]
				// },
				{
					image: 'defaults', 
					label: 'DEFAULTS',
					is_parent: true,
					opened: false,
					children: [
						{ routerLink: '/main/home_screen_messages', image: 'defaults', label: 'HOME SCREEN MESSAGES' },
						{ routerLink: '/main/subscription_text', image: 'defaults', label: 'SUBSCRIPTION TEXT' },
						{ routerLink: '/main/uber_status', image: 'defaults', label: 'UBER STATUS' },
						{ routerLink: '/main/versions', image: 'defaults', label: 'VERSIONS' },
					]
				},
			]
		}
		$("a.nav-dropdown-trigger").on("click", function (e: any) {
			e.preventDefault();
			var $this = $(this);
			$this.parent(".sub-nav").toggleClass("opened");
		});

		$(".menu-trigger").on("click", function (e: any) {
			e.preventDefault();
			var $this = $(this);
			$this.toggleClass("active");
			$this.parents(".dashboard-main").find(".page-sidebar").toggleClass("opened");
			$this.parents(".dashboard-main").find(".page-content").toggleClass("opened");
		});

		this.getCounts();
	}

	getCounts(): void
	{
		clearTimeout(this.timer);

		this.getNotificationCount();

		this.timer = setTimeout(() => {
			this.getCounts();
		}, 8000);

	}

	getNotificationCount(): void
	{
		let maxId: any = localStorage.getItem('MaxId');
		if(maxId == void 0 || maxId == 0)
		{
			// maxId = 0;
			return;
		}
		this.mainApiService.getList(appConfig.base_url_slug + 'getCounts?maxId='+ maxId).then(response => {
			if(response.status == 200)
			{
				this.Notifications = response.data;
			}
			else if (response.status == 404)
			{
				this.Notifications = {
					ordersCount: 0,
					maxId: 0
				}
			}
			else
			{

			}
		});
	}

	ngOnDestroy() {
		$(".sidebar-nav a.nav-dropdown-trigger").off("click", function (e: any) {
			e.preventDefault();
		});
	}

	onLogout(): void {
		this.mainApiService.onLogout().then (result => {
			if (result.status === 200  && result.data) {
				localStorage.clear();
				this.adminClick = false;
				window.location.reload();
			}
		});
	}

	onHomeClick(): void 
	{
		this.router.navigate(['/main']);
	}

	onNotificationClick(): void
	{
		if(this.Notifications.ordersCount == 0)
		{
			return;
		}
		this.Notifications = {
			ordersCount: 0,
			maxId: 0
		}
		this.router.navigateByUrl('/main/orders');
		// this.ordersComponent.ngOnInit();
	}

	onMenuClick(menu, event): void
	{
		localStorage.removeItem('componentSettings');
		event.preventDefault();
		event.stopPropagation();
		
		this.menus.forEach(element => {
			if(element.label != menu.label)
			{
				element.opened = false;
			}
		});
		menu.opened = !menu.opened;

		// console.log('Parent', menu);
		if(menu.label == 'Orders')
		{
			this.Notifications = {
				ordersCount: 0,
				maxId: 0
			}
		}
	}

	onChildClick(menu, event): void
	{
		event.preventDefault();
		event.stopPropagation();
		// console.log('Child', menu);
	}
}


