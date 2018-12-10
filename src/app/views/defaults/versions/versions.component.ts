import { Component, OnInit } from '@angular/core';
import { MainService } from '../../../services/main.service';
import { MatDialog } from '@angular/material';
import { VersionDialog } from './version.dialog';
import { BaseLoaderService } from '../../../services';
import { appConfig } from '../../../../config';

@Component({
  selector: 'app-versions',
  templateUrl: './versions.component.html'
})
export class VersionsComponent implements OnInit 
{
    Versions: any;
    data: any;

	constructor(protected mainApiService: MainService, 
		protected dialog: MatDialog, 
		protected loaderService: BaseLoaderService) 
	{
        this.Versions = null;
        this.data = {
            type: '',
            version: 0,
            forcefully_updated: 0
        };
        // this.Versions = {
        //     id:1,
        //     version_ios:1.00,
        //     forcefully_updated_ios:0,
        //     version_android:1.00,
        //     forcefully_updated_android:0,
        //     created_at:'2018-07-18 09:53:37',
        //     updated_at:'2018-07-19 05:58:03'
        // }
    }
    

	ngOnInit() 
	{
		this.getVersionsList();
	}

	onEditSetting(type): void 
	{
        let data = null;
        if(type == 'android')
        {
            this.data['type'] = type;
            this.data['version'] = this.Versions.version_android;
            this.data['forcefully_updated'] = this.Versions.forcefully_updated_android;
        }
        else if(type == 'ios')
        {
            this.data['type'] = type;
            this.data['version'] = this.Versions.version_ios;
            this.data['forcefully_updated'] = this.Versions.forcefully_updated_ios;
        }

        this.data['upDown'] = 1;

        let dialogRef = this.dialog.open(VersionDialog, {autoFocus: false});
        dialogRef.componentInstance.platform = this.data;
		 dialogRef.afterClosed().subscribe(result => {
			 if(result)
			 {
				this.getVersionsList();
			 }
		 })
	}

	SettingChange()
	{
		this.getVersionsList();
	}

	getVersionsList(isLoaderHidden?: boolean): void
	{
		this.Versions = null;
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}
		let url = 'getVersion';
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
                // console.log(result)
                this.Versions = result.data;
				// for(let key in result.data)
				// {
				// 	let name = key.split("_").join(" ");
				// 	name = this.toTitleCase(name)
				// 	this.Versions.push({key: key, value: result.data[key], name: name})
				// }
				// console.log(this.Versions);
				this.loaderService.setLoading(false);
			}
			else
			{
				this.Versions = [];
				this.loaderService.setLoading(false);
			}
		});
	}

	toTitleCase(str) 
	{
		return str.replace(/\w\S*/g, function(txt)
		{
			return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
		});
	}

}
