<template>
    <app-layout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="leading-loose">
                    <form class="max-w-xl p-10 bg-white rounded shadow-xl mx-auto">
                        <p class="text-gray-800 font-medium">Payment information</p>
                        <div id="card_element"></div>
                        <div class="mt-4">
                            <button type="button" :class="{'bg-black' : paymentProcessing}"
                                class="px-4 w-full py-1 text-white font-light tracking-wider bg-purple-500
                                rounded">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Welcome from '@/Jetstream/Welcome.vue'
    import { loadStripe } from '@stripe/stripe-js'

    export default {
        components: {
            AppLayout,
            Welcome,
        },
        data () {
            return {
                stripe: {},
                cardElement: {},
                paymentProcessing: false
            }
        },
        async mounted() {
            this.stripe = await loadStripe(process.env.MIX_STRIPE_KEY);

            const elements = this.stripe.elements();
            this.cardElement = elements.create('card', {
                classes: {
                    base: 'w-full px-2 py-4 text-gray-700 bg-gray-200 rounded hover:shadow-md shadow-inner transition-shadow duration-150 ease-in ' +
                        'focus:shadow-lg shadow-inner'
                }
            });

            this.cardElement.mount('#card_element');
        },
        methods: {
            async processPayment() {
                // send Payment information to laravel and strip
                this.paymentProcessing = true;

                const {paymentMehod, error} = await this.stripe.createPaymentMethod(
                    'card', this.cardElement, {
                        billing_details: {
                            name: "Haider Shah",
                            email: "stfox003@gmail.com",
                            address: {
                                line1: '289 - 6 c-3 green town',
                                city: 'lahore',
                                state: 'Punjab',
                                postal_code: '54700'
                            }
                        }
                    }
                );
                if (error) {
                    this.paymentProcessing = false;
                    alert(error);
                } else {
                    this.customer.paymentProcessing = true;
                }
            }
        }
    }
</script>
