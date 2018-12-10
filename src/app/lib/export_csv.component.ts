import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material';

import { MainService } from '../services/main.service';
import { ExportCSVDialog } from './export_csv';
import { AppLoaderService } from './app-loader/app-loader.service';
import { appConfig } from '../../config';


@Component({
	template: ``
})
export class ExportCSVComponent implements OnInit 
{
	ArrayCSV: any[];
	loaderMessage: string;
	start_date: string;
	end_date: string;
    ArrayCSVCount: any;
	method: string;
	otherPerPage: any;
	parent_id: string;
	isNeededDate: boolean;
	
	constructor( protected mainApiService: MainService, protected appLoaderService: AppLoaderService, protected dialog: MatDialog)	
	{
		this.ArrayCSV = [];
		this.loaderMessage = "Please wait CSV file is preparing to download.";
		this.ArrayCSVCount = 0;
		this.otherPerPage = 0;
		this.parent_id = null;
		this.isNeededDate = true;
	}

	ngOnInit() 
	{
		
	}

	onExportCSV(): void
	{
		if(this.isNeededDate)
		{
			let dialogRef = this.dialog.open(ExportCSVDialog, {autoFocus: false});
			dialogRef.afterClosed().subscribe(result => 
			{
				if(result != false && result != void 0)
				{
					this.start_date = result.start_date;
					this.end_date = result.end_date;

					this.getCountData();
				}
			})
		}
		else
		{
			this.getCountData();
		}
	}

	getCountData(): void
	{
		let url: any = appConfig.base_url_slug + this.method + '?index='+ 1 + '&index2='+ 5;

		if(this.isNeededDate)
		{
			url = url + '&start_date='+ this.start_date + '&end_date='+ this.end_date;
		}
		
		if(this.parent_id != null)
		{
			url = url + '&id=' + this.parent_id;
		}

		this.mainApiService.getList(url).then(result => 
		{
			if (result.status === 200  && result.data) 
			{
				if(this.method == 'getMerchants')
				{
					this.ArrayCSVCount = result.data.merchantsCount;
				}
				else if(this.method == 'getOutlets')
				{
					this.ArrayCSVCount = result.data.outletsCount;
				}
				else if(this.method == 'getOffers')
				{
					this.ArrayCSVCount = result.data.offersCount;
				}
				else if(this.method == 'getUsers')
				{
					this.ArrayCSVCount = result.data.usersCount;
				}
				else if(this.method == 'getOrders')
				{
					this.ArrayCSVCount = result.data.allOrdersCount;
				}
				else if(this.method == 'getSubscriptions')
				{
					this.ArrayCSVCount = result.data.subscriptionsCount;
				}
				else if(this.method == 'getNonUsers')
				{
					this.ArrayCSVCount = result.data.usersCount;
				}
				else if(this.method == 'getMultipleAccessCode')
				{
					this.ArrayCSVCount = result.data.accesscodesCount;
				}
				
				this.appLoaderService.setLoading(true);
				this.ArrayCSV = [];
				let index = 1, perPage = 2000, loopIndex = 0;
				let totalCalls = Math.round(this.ArrayCSVCount / perPage);
				// perPage = Math.round(this.ArrayCSVCount / totalCalls);
				if(totalCalls < (this.ArrayCSVCount / perPage))
				{
					totalCalls = totalCalls + 1;
				}
				this.myLoop(loopIndex, index, totalCalls, perPage);
			}
			else
			{
				// this.ArrayCSV = [];
			}
		});
	}

	myLoop (loopIndex, index, totalCalls, perPage) 
	{
		// console.log(this.ArrayCSV);

		// this.otherPerPage = this.ArrayCSVCount - this.ArrayCSV.length;
		// if(this.otherPerPage < 2000)
		// {
		// 	perPage = this.otherPerPage;
		// }
		let url = appConfig.base_url_slug +this.method + '?index='+ index + '&index2='+ perPage + '&start_date='+ this.start_date + '&end_date='+ this.end_date;
		if(this.parent_id != null)
		{
			url = url + '&id=' + this.parent_id;
		}

		this.mainApiService.getList(url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
                let usersData: any, csvName: any;

                if(this.method == 'getMerchants')
                {
                    csvName = 'merchants.csv';
                    usersData = result.data.merchants;
                }
                else if(this.method == 'getOutlets')
                {
                    csvName = 'outlets.csv';
                    usersData = result.data.outlets;
                }
                else if(this.method == 'getOffers')
                {
                    csvName = 'deals.csv';
                    usersData = result.data.offers;
                }
                else if(this.method == 'getUsers')
                {
                    csvName = 'registered_customers.csv';
                    usersData = result.data.users;
				}
				else if(this.method == 'getOrders')
				{
					csvName = 'orders.csv';
                    usersData = result.data.allOrders;
				}
				else if(this.method == 'getNonUsers')
                {
                    csvName = 'non_registered_customers.csv';
                    usersData = result.data.users;
				}
				else if(this.method == 'getMultipleAccessCode')
                {
                    csvName = 'multiple_access_codes.csv';
                    usersData = result.data.accesscodes;
				}
				else if(this.method == 'getSubscriptions')
				{
					csvName = 'subscriptions.csv';
					usersData = result.data.subscriptions;
					
					usersData.forEach(element => {
						
						element.accesscode = element.accesscodes.code;
						if(element.accesscode == void 0)
						{
							element.accesscode = null;
						}
						
						delete element.accesscodes;
					});
				}

				usersData.forEach(element => {
					this.ArrayCSV.push(element);
				});

				loopIndex++;
                index++;
                
				if (loopIndex < totalCalls) 
				{ 
					this.myLoop(loopIndex, index, totalCalls, perPage);
					if(loopIndex == totalCalls -1)
					{
						this.loaderMessage = "Your file is downloading."
					}
				}
				else
				{
					console.log('Loop Finished, Your File is Downloading.');

					let csvContent = "";

					this.ArrayCSV.forEach((rowArray, index) =>
					{
						var line = '';
						var header = '';
						for (var key in rowArray) 
						{
							if(rowArray[key] != null && typeof rowArray[key] === 'string')
							{
								try {
									rowArray[key] = rowArray[key].split(',').join(';');
									rowArray[key] = rowArray[key].split('\n').join('||');
									rowArray[key] = rowArray[key].split('\r').join('||');
								} catch (error) {
									console.log(error);
									console.log(key, rowArray[key])
								}
							}
							if(index == 0)
							{
								header +=  key + ',';
							}
							line +=  rowArray[key] + ',';
						}
						if(index == 0)
						{
							csvContent += header  + '\r\n' + line + '\r\n';
						}
						else
						{
							csvContent += line + '\r\n';
						}
					});

					// console.log(this.ArrayCSV)
					// console.log(csvContent.split('\r\n'))
					
					this.downloadFile(csvContent, csvName);
				}
			}
			else
			{
				this.ArrayCSV = [];
			}
		});
	}

	downloadFile(data, fileName) 
	{
		this.loaderMessage = "Please wait CSV file is preparing to download.";
		this.appLoaderService.setLoading(false);
        var csvData = data;
        var blob = new Blob([ csvData ], {
            type : "application/csv;charset=utf-8;"
        });

		if (window.navigator.msSaveBlob) 
		{
            navigator.msSaveBlob(blob, fileName);
		} 
		else 
		{
            // FOR OTHER BROWSERS
            var link = document.createElement("a");
            var csvUrl = URL.createObjectURL(blob);
            link.href = csvUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
	}

}
