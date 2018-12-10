import { Component, OnInit, ElementRef, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog, MatDialogRef } from '@angular/material';

import { MainService, BaseLoaderService } from '../../services';


@Component({
	selector: 'app-notification-details',
	templateUrl: './notification-details.component.html'
})
export class NotificationDetailsComponent implements OnInit, AfterViewInit
{
	
	sub: Subscription;
	Notification: any;
    Notifications: any;
    status: boolean;

	constructor(private elRef: ElementRef, protected router: Router,
		protected mainApiService: MainService,
		protected loaderService: BaseLoaderService, protected dialog: MatDialog, protected dialogRef: MatDialogRef<NotificationDetailsComponent>)	
	{
        this.Notification = null;
        this.Notifications = [];
        this.status = false;
	}

	ngOnInit() 
	{
		// this.getNotifications();
		console.log(this.Notification);
        if(this.Notification.active == 1)
        {
            this.status = true;
        }
        else if(this.Notification.active == 0)
        {
            this.status = false;
        }
	}
	
	getUsers(users): any
	{
		let all = users.split(',').join(', ');
		return all;
	}

    ngAfterViewInit()
    {
        this.elRef.nativeElement.parentElement.classList.add("mat-dialog-changes-1");
    }

    
    onEditNotification(): void 
	{
		localStorage.setItem('Notification', JSON.stringify(this.Notification));
        this.router.navigateByUrl('main/notifications/' + this.Notification.id);
        this.dialogRef.close();
	}
}
