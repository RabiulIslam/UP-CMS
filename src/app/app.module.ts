
import { HashLocationStrategy, LocationStrategy } from '@angular/common';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AgmCoreModule, GoogleMapsAPIWrapper } from '@agm/core';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { AuthGuard } from './guards/auth.guard';
import { MainAuthGuard } from './guards/main-auth.guard';
import { FlexLayoutModule } from "@angular/flex-layout";
import { MalihuScrollbarModule } from 'ngx-malihu-scrollbar';
import { AgularMaterialModule } from './material.module';

// Services
import { AuthService, MainService, PaginationService, BaseLoaderService } from './services';

// Lib
import { FilterDatePipe, AlertDialog, BaseLoaderComponent, PaginationComponent, MapLocationDialog, GetLocationDialog, FilePickerComponent, ExportCSVDialog, MultiTagInputComponent } from './lib';

// Config
import { appConfig } from '../config';

// Directives
import { ClickOutsideDirective } from './directives/click-outside.directive';

// Page Components
import { LoginComponent } from './views/auth/login/login.component';
import { TemplateMainApp } from './templates/template-main-app/template-main-app.component';
import { ForgotPasswordComponent } from './views/auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './views/auth/reset-password/reset-password.component';
import { AdminsListComponent, AdminsFormComponent } from './views/admins';
import { MerchantsFormComponent, MerchantsListComponent, MerchantDetailsComponent, MultipleOutletsFormComponent } from './views/merchants';
import { DealsFormComponent, DealsListComponent, DealDetailsComponent } from './views/deals';
import { OutletsListComponent, OutletsFormComponent, OutletDetailsComponent } from './views/outlets';
import { CustomersListComponent } from './views/customers';
import { AccessCodesFormComponent, AccessCodesListComponent, ViewCodesComponent } from './views/access_codes';
import { OrdersListComponent } from './views/orders';
import { NotificationsFormComponent, NotificationsListComponent } from './views/notifications';
// import { AutomatedNotificationsFormComponent, AutomatedNotificationsListComponent } from './views/notifications/automated_notifications';
import { VersionsComponent, VersionDialog } from './views/defaults/versions';
import { HomeScreenMessagesComponent, HomeScreenMessagesDialog } from './views/defaults/home_screen_messages';
import { SubscriptionTextComponent, SubscriptionTextDialog } from './views/defaults/subscription_text';
import { UberStatusComponent } from './views/defaults/uber_status';
import { TagInputModule } from 'ngx-chips';
import {NgxMaskModule} from 'ngx-mask'
import { OwlDateTimeModule, OwlNativeDateTimeModule } from 'ng-pick-datetime';
import { SubscriptionsListComponent } from './views/subscriptions/subscriptions.component';
import { SubscriptionLogsComponent } from './views/subscriptions/subscriptions-logs/subscriptions-logs.component';
import { OrderReviewsComponent } from './views/order_reviews';
import { CodeDetailsComponent } from './views/subscriptions';
import { AppLoaderComponent } from './lib/app-loader/app-loader';
import { AppLoaderService } from './lib/app-loader/app-loader.service';
import { MultipleDealsFormComponent } from './views/outlets/multiple_deals.component';
import { ExportCSVComponent } from './lib/export_csv.component';
import { NotificationDetailsComponent } from './views/notifications/notification-details.component';
import { CustomersFormComponent } from './views/customers/customers-form.component';
import { EditSelectedComponent } from './views/deals/edit-selected.component';
import { ImportCSVComponent } from './lib/import_csv.component';
import { CustomerDetailsComponent } from './views/customers/customer-details.component';


@NgModule({
    imports: [
        BrowserModule,
        FormsModule,
        // HttpModule,
        TagInputModule,
        HttpClientModule,
        AppRoutingModule,
        ReactiveFormsModule,
        BrowserAnimationsModule,
        AgularMaterialModule,
        AgmCoreModule.forRoot({
            apiKey: appConfig.google_api_key,
            libraries: ['places']
        }),
        FlexLayoutModule,
        MalihuScrollbarModule.forRoot(),
        // NgxEditorModule,
        NgxMaskModule.forRoot(),
        OwlDateTimeModule, 
        OwlNativeDateTimeModule,
        // NgProgressModule,
    ],
    providers: [
        AuthGuard,
        AuthService,
        MainService,
        MainAuthGuard,
        PaginationService,
        BaseLoaderService,
        GoogleMapsAPIWrapper,
        AppLoaderService,
        { provide: LocationStrategy, useClass: HashLocationStrategy },
        // { provide: OWL_DATE_TIME_FORMATS, useValue: 'fr' },
        // {provide: HTTP_INTERCEPTORS, useClass: NgProgressInterceptor, multi: true },
    ],
    declarations: [
        AppComponent,
        LoginComponent,
        TemplateMainApp,
        AlertDialog,
        ClickOutsideDirective,
        BaseLoaderComponent,
        PaginationComponent,
        ForgotPasswordComponent,
        ResetPasswordComponent,
        MapLocationDialog,
        GetLocationDialog,
        FilePickerComponent,
        AdminsListComponent,
        AdminsFormComponent,
        MerchantsFormComponent, MerchantsListComponent,
        DealsFormComponent, DealsListComponent,
        OutletsListComponent, OutletsFormComponent, OutletDetailsComponent,
        AccessCodesFormComponent, AccessCodesListComponent,
        CustomersListComponent,
        OrdersListComponent, 
        NotificationsFormComponent, NotificationsListComponent,
        VersionsComponent, VersionDialog,
        // AutomatedNotificationsFormComponent, AutomatedNotificationsListComponent,
        HomeScreenMessagesComponent, HomeScreenMessagesDialog,
        SubscriptionTextComponent, SubscriptionTextDialog,
        UberStatusComponent,
        FilterDatePipe,
        SubscriptionsListComponent,
        SubscriptionLogsComponent,
        OrderReviewsComponent,
        DealDetailsComponent,
        MerchantDetailsComponent,
        ViewCodesComponent,
        CodeDetailsComponent,
        MultipleOutletsFormComponent,
        AppLoaderComponent,
        MultipleDealsFormComponent,
        ExportCSVDialog,
        ExportCSVComponent,
        ImportCSVComponent,
        NotificationDetailsComponent,
        CustomersFormComponent,
        EditSelectedComponent,
        MultiTagInputComponent,
        CustomerDetailsComponent
    ],
    entryComponents: [
        AlertDialog,
        MapLocationDialog,
        GetLocationDialog,
        VersionDialog,
        HomeScreenMessagesDialog, SubscriptionTextDialog,
        OutletDetailsComponent,
        DealDetailsComponent,
        MerchantDetailsComponent,
        ViewCodesComponent,
        CodeDetailsComponent,
        ExportCSVDialog,
        NotificationDetailsComponent,
        EditSelectedComponent,
        CustomerDetailsComponent
    ],
    bootstrap: [AppComponent]
})
export class AppModule { }


