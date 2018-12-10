
import { Component, OnInit, Input } from '@angular/core';
import { Router } from '@angular/router';

import { AuthService } from '../../../services/auth.service';
import { FormGroup, FormBuilder, Validators, FormControl } from '@angular/forms';

@Component({
    selector: 'app-login',
    templateUrl: 'login.component.html',
    styleUrls: ['../auth.css']
})
export class LoginComponent implements OnInit 
{
    form_submitting = false;
    failureAlert = false;
    btnLoading = false;
    alertMsg = "";
    theLoginForm: FormGroup;
    @Input() data;

    constructor(private router: Router, private authService: AuthService, protected formbuilder: FormBuilder) 
    {
        this.theLoginForm = this.formbuilder.group({
            email: [null, [Validators.required, Validators.email]],
            password: [null,[Validators.minLength(6), Validators.required]],
            type: ['admin']
        })
    }

    closealert() 
    {
        // this.failureAlert = false;
    }

    ngOnInit() 
    {
        // this.deviceToken = localStorage.getItem('deviceToken');
    }

    get email() 
    { 
        return this.theLoginForm.get('email'); 
    }

    get password() 
    { 
        return this.theLoginForm.get('password'); 
    }

    doLogin() 
    {
        this.btnLoading = true;

        this.authService.login(this.theLoginForm.value).then(response => {
            if (response.status == 200) 
            {
                // console.log(response)
                // if (response.status === 200) 
                // {
                    this.router.navigate(['/main']);
                // }
               
            }
            else 
            {
                this.btnLoading = false;
                this.failureAlert = true;
                // this.alertMsg = response.message;
                this.alertMsg = "Email or Password is incorrect, try again or click on forgot password to reset it.";

                setTimeout(function ()
                {
                    this.failureAlert = false;
                }.bind(this), 2500);
            }
        }, 
        Error => {
            // // console.log("LOGIN: ",Error)
            this.btnLoading = false;
            this.failureAlert = true;
            this.alertMsg = "Email or Password is incorrect, try again or click on forgot password to reset it.";

            setTimeout(function () 
            {
                this.failureAlert = false;
            }.bind(this), 3000);
        });
       
        // this.authService.login(this.theLoginForm.value)
        //     .subscribe(result => {
        //         // console.log(result)
        //         if (result.status === 200  && result.data) 
        //         {
        //             this.router.navigate(['/main']);
        //         }
        //         else 
        //         {
        //             this.btnLoading = false;
        //             this.failureAlert = true;
        //             this.alertMsg = result.message;

        //             setTimeout(function ()
        //             {
        //                 this.failureAlert = false;
        //             }.bind(this), 2500);
        //         }
        //     }, 
        //     Error => {
        //         // // console.log("LOGIN: ",Error)
        //         this.btnLoading = false;
        //         this.failureAlert = true;
        //         this.alertMsg = "Email or Password is incorrect, try again or click on forgot password to reset it.";

        //         setTimeout(function () 
        //         {
        //             this.failureAlert = false;
        //         }.bind(this), 3000);
        //     });
    }
}


