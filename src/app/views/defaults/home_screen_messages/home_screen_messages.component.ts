import { Component, OnInit } from '@angular/core';
import { MainService } from '../../../services/main.service';
import { MatDialog } from '@angular/material';
import { HomeScreenMessagesDialog } from './home_screen_messages.dialog';
import { BaseLoaderService } from '../../../services';
import { appConfig } from '../../../../config';

@Component({
  selector: 'app-home_screen_messages',
  templateUrl: './home_screen_messages.component.html'
})
export class HomeScreenMessagesComponent implements OnInit 
{
    HomeScreenMessage: any;

	constructor(protected mainApiService: MainService, 
		protected dialog: MatDialog, 
		protected loaderService: BaseLoaderService) 
	{
        this.HomeScreenMessage = null;
    }
    

	ngOnInit() 
	{
		this.getHomeScreenMessagesList();
	}

	onEditSetting(type): void 
	{
        let dialogRef = this.dialog.open(HomeScreenMessagesDialog, {autoFocus: false});
        dialogRef.componentInstance.homeScreenMessage = this.HomeScreenMessage.home_page_text;
		 dialogRef.afterClosed().subscribe(result => {
			 if(result)
			 {
				this.getHomeScreenMessagesList();
			 }
		 })
	}

	SettingChange()
	{
		this.getHomeScreenMessagesList();
	}

	getHomeScreenMessagesList(isLoaderHidden?: boolean): void
	{
		this.HomeScreenMessage = null;
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}
		let url = 'getDefaults';
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
                this.HomeScreenMessage = result.data;
				this.loaderService.setLoading(false);
			}
			else
			{
				this.HomeScreenMessage = null;
				this.loaderService.setLoading(false);
			}
		});
	}
}
