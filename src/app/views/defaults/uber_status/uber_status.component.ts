import { Component, OnInit } from '@angular/core';
import { MainService } from '../../../services/main.service';
import { MatDialog } from '@angular/material';
import { BaseLoaderService } from '../../../services';
import { appConfig } from '../../../../config';

@Component({
  selector: 'app-uber_status',
  templateUrl: './uber_status.component.html'
})
export class UberStatusComponent implements OnInit 
{
	UberStatus: any;
	uberValue: any;

	constructor(protected mainApiService: MainService, 
		protected dialog: MatDialog, 
		protected loaderService: BaseLoaderService) 
	{
        this.UberStatus = null;
    }
    

	ngOnInit() 
	{
		this.getUberStatusList();
	}

	getUberStatusList(isLoaderHidden?: boolean): void
	{
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
                console.log(result)
                this.UberStatus = result.data;
				this.loaderService.setLoading(false);
				if(this.UberStatus.uber == 0)
				{
					this.uberValue = false;
				}
				else
				{
					this.uberValue = true;
				}
			}
			else
			{
				this.UberStatus = null;
				this.loaderService.setLoading(false);
			}
		});
	}

	updateUberStatusList(): void
	{
		let url = 'addUpdateDefault';

		let dict = {
			type: 'uber',
			uber: 0
		}

		if(this.uberValue)
		{
			dict.uber = 1;
		}
		else
		{
			dict.uber = 0;
		}
		
		this.mainApiService.postData(appConfig.base_url_slug + url, dict)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
                console.log(result)
                this.UberStatus = result.data;
			}
			else
			{
				this.UberStatus = null;
			}
		});
	}
}
