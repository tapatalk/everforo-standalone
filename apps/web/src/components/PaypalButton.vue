<template>
    <div id="paypal-button"></div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {loadScript} from '@/helpers/Utils';

    @Component({
        components: {

        },
    })
    export default class PaypalButton extends Vue {

        @Prop()
        protected orderId!: string;

        private paypalPayUrl: string = '';
        private paypalExecuteUrl: string = '';

        protected created() {
            this.paypalPayUrl = process.env.VUE_APP_API_URL + '/api/paypal/gettoken';
            this.paypalExecuteUrl = process.env.VUE_APP_API_URL + '/api/paypal/confirm';

            // load paypal SDK url
            // currency=USD&intent=order&commit=false&vault=true&
            loadScript('https://www.paypal.com/sdk/js?disable-funding=credit,card&client-id=' + process.env.VUE_APP_PAYPAL_CLIENT_ID)
            .then(() => {

                (window as any).paypal.Buttons({
                    style: {
                        color: 'blue',
                        shape: 'rect',
                        label: 'paypal',
                        height: 32,
                    },
                    createOrder: () => {
                
                        return fetch(this.paypalPayUrl, {
                            method: 'post',
                            headers: {
                                'content-type': 'application/json',
                                'Authorization': 'Bearer ' + localStorage.getItem('bearer'),
                            },
                            body: JSON.stringify({
                                order_id: this.orderId,
                            })
                        }).then(function(res: any) {

                            return res.json();
                        }).then((response: {data: {token: string}}) => {
                            // this is the actual response from API    
                            return response.data.token;
                        });
                    },

                    onApprove: (data: {paymentID: string, payerID: string}) => {
                        var EXECUTE_URL = this.paypalExecuteUrl;

                        return fetch(EXECUTE_URL, {
                            method: 'post',
                            headers: {
                                'content-type': 'application/json',
                                'Authorization': 'Bearer ' + localStorage.getItem('bearer'),
                            },
                            body: JSON.stringify({
                                paymentID: data.paymentID,
                                payerID:   data.payerID
                            })
                        }).then((res: any) => {
                    
                            return res.json();
                        }).then((response: {data: {state: string, id: string}}) => {
                            
                            if (response.data && response.data.state == 'approved') {
                                this.paymentSuccessful();
                            }
                        });
                    },

                    onCancel: (data: {orderID: string}) => {
                        console.info(data.orderID);
                    },

                    // onError: (a,b,c) => {
                        // console.log('error',a,b,c);
                    // },

                }).render('#paypal-button');
            })
            .catch(() => {

            });
        }

        @Emit('payment-success')
        protected paymentSuccessful() {
            
        }
    }
</script>
<style lang="scss" scoped>
    #paypal-button {
        width: 150px; // minimum possoble width of paypal button
        height: 32px; // button style.height
        border-radius: $border-radius1;
        overflow: hidden;
    }
</style>
