
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';


import { AppComponent } from './app.component';
import { LoginComponent } from './views/auth/login/login.component';
import { TemplateMainApp } from './templates/template-main-app/template-main-app.component';

import { AuthGuard, MainAuthGuard } from './guards';
import { ForgotPasswordComponent } from './views/auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './views/auth/reset-password/reset-password.component';
import { AdminsListComponent, AdminsFormComponent } from './views/admins';
import { MerchantsFormComponent, MerchantsListComponent, MultipleOutletsFormComponent } from './views/merchants';
import { DealsFormComponent, DealsListComponent } from './views/deals';
import { OutletsFormComponent, OutletsListComponent } from './views/outlets';
import { CustomersListComponent } from './views/customers';
import { AccessCodesFormComponent, AccessCodesListComponent } from './views/access_codes';
import { OrdersListComponent } from './views/orders';
import { NotificationsListComponent, NotificationsFormComponent } from './views/notifications';
// import { AutomatedNotificationsFormComponent, AutomatedNotificationsListComponent } from './views/notifications/automated_notifications';
import { VersionsComponent } from './views/defaults/versions';
import { HomeScreenMessagesComponent } from './views/defaults/home_screen_messages';
import { SubscriptionTextComponent } from './views/defaults/subscription_text';
import { UberStatusComponent } from './views/defaults/uber_status';
import { SubscriptionsListComponent } from './views/subscriptions/subscriptions.component';
import { SubscriptionLogsComponent } from './views/subscriptions/subscriptions-logs/subscriptions-logs.component';
import { OrderReviewsComponent } from './views/order_reviews';
import { MultipleDealsFormComponent } from './views/outlets/multiple_deals.component';
import { CustomersFormComponent } from './views/customers/customers-form.component';


const publicRoutes: Routes = [
	{ path: '', redirectTo: 'login', pathMatch: 'full' },
	{ path: 'login', component: LoginComponent },
	{ path: 'forgot-password', component: ForgotPasswordComponent },
	{ path: 'reset-password', component: ResetPasswordComponent },
	{ path: 'reset-pin', component: ResetPasswordComponent },
];

const mainApp: Routes = [
	{ path: '', redirectTo: 'merchants', pathMatch: 'full' },

	{ path: 'merchants', component: MerchantsListComponent },
	{ path: 'merchants/:id', component: MerchantsFormComponent },
	{ path: 'merchants/multiple_outlets/:id', component: MultipleOutletsFormComponent },

	{ path: 'outlets', component: OutletsListComponent },
	{ path: 'outlets/:id', component: OutletsFormComponent },
	{ path: 'outlets/multiple_deals/:id', component: MultipleDealsFormComponent },

	{ path: 'deals', component: DealsListComponent },
	{ path: 'deals/:id', component: DealsFormComponent },

	{ path: 'access_codes', component: AccessCodesListComponent },
	{ path: 'access_codes/:id', component: AccessCodesFormComponent },

	{ path: 'admins', component: AdminsListComponent },
	{ path: 'admins/:id', component: AdminsFormComponent },

	{ path: 'notifications', component: NotificationsListComponent },
	{ path: 'notifications/:id', component: NotificationsFormComponent },
	
	// { path: 'subscriptions', component: NotificationsListComponent },
	{ path: 'customers/subscriptions/:id', component: SubscriptionsListComponent },
	{ path: 'subscriptions/:id', component: SubscriptionsListComponent },

	{ path: 'customers/orders/:id', component: OrdersListComponent },
	{ path: 'customers/orders/:id/reviews/:order_id', component: OrderReviewsComponent },
	{ path: 'orders/:id', component: OrdersListComponent },
	{ path: 'orders/:id/reviews/:order_id', component: OrderReviewsComponent },

	{ path: 'customers/subscription_logs/:id', component: SubscriptionLogsComponent },

	// { path: 'automated_notifications', component: AutomatedNotificationsListComponent },
	// { path: 'automated_notifications/:id', component: AutomatedNotificationsFormComponent },

	{ path: 'customers/:type', component: CustomersListComponent },
	{ path: 'customers/edit/:id', component: CustomersFormComponent },

	{ path: 'versions', component: VersionsComponent },
	{ path: 'home_screen_messages', component: HomeScreenMessagesComponent },
	{ path: 'subscription_text', component: SubscriptionTextComponent },
	{ path: 'uber_status', component: UberStatusComponent },
];

const appRoutes: Routes = [
	{ path: '', redirectTo: '/auth/login', pathMatch: 'full' },
	{ path: 'auth', component: AppComponent, children: publicRoutes, canActivate: [AuthGuard] },
	{ path: 'main', component: TemplateMainApp, children: mainApp, canActivate: [MainAuthGuard] },
	// { path: 'main', component: TemplateMainApp, children: mainApp },
];


@NgModule({
	imports: [RouterModule.forRoot(appRoutes)],
	exports: [RouterModule],
	providers: []
})
export class AppRoutingModule { }
