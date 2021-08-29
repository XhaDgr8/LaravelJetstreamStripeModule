<template>
    <div class="w-2/4 p-4 bg-white rounded shadow-xl">
        <p class="text-gray-800 font-medium">Customer information</p>
        <labeled-input :error="this.form.errors.name" :disable="paymentProcessing" v-model="form.name" group_class="" label_text="Name"/>

        <labeled-input :error="this.form.errors.email" :disable="paymentProcessing" type="email" v-model="form.email" group_class="mt-2" label_text="Email"/>

        <labeled-input :error="this.form.errors.password" :disable="paymentProcessing" type="password" group_class="mt-2" v-model="form.password" label_text="Password"/>

        <div class="mt-4" v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
            <jet-label for="terms">
                <div class="flex items-center">
                    <jet-checkbox name="terms" id="terms" v-model:checked="form.terms" />

                    <div class="ml-2">
                        I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900">Privacy Policy</a>
                    </div>
                </div>
            </jet-label>
        </div>
        <labeled-input :error="this.form.errors.address" :disable="paymentProcessing" group_class="mt-2" v-model="form.address" label_text="Address"/>

        <div class="flex">
            <labeled-input :error="this.form.errors.city" :disable="paymentProcessing" group_class="mt-2 mr-1" v-model="form.city" label_text="City"/>

            <labeled-input :error="this.form.errors.country" :disable="paymentProcessing" group_class="mt-2 ml-1" v-model="form.country" label_text="Country"/>
        </div>

        <div class="flex">
            <labeled-input :error="this.form.errors.state" :disable="paymentProcessing" group_class="mt-2 mr-1" v-model="form.state" label_text="State"/>

            <labeled-input :error="this.form.errors.zip_code" :disable="paymentProcessing" group_class="mt-2 ml-1" v-model="form.zip_code" label_text="Zip"/>
        </div>

        <p class="mt-4 text-gray-800 font-medium">Payment information</p>

        <div id="card_element"></div>

        <div class="mt-4">
            <button type="button" :class="{'bg-black' : paymentProcessing}"
                    class="px-4 w-full py-1 text-white font-light tracking-wider bg-gray-900 rounded
                        transition-shadow duration-150 ease-in hover:shadow-lg focus:shadow-lg shadow-inner"
                    @click="processPayment" :disabled="paymentProcessing"
                    v-text="paymentProcessing ? 'Processing' : 'Pay Now'">
            </button>
        </div>
    </div>
</template>

<script>
import { loadStripe } from '@stripe/stripe-js'
import LabeledInput from "@/Components/LabeledInput";
export default {
    name: "PaymentProcessor",
    components: {LabeledInput},
    data () {
        return {
            stripe: {},
            cardElement: {},
            paymentProcessing: false,
            form: this.$inertia.form({
                payment_method_id: '',
                amount: '',
                name: '',
                email: '',
                password: '',
                address: '',
                country: '',
                city: '',
                state: '',
                zip_code: '',
                terms: false,
            }),
        }
    },
    async mounted() {
        this.stripe = await loadStripe(process.env.MIX_STRIPE_KEY);

        const elements = this.stripe.elements();
        this.cardElement = elements.create('card', {
            classes: {
                base: 'w-full px-5 py-2 text-gray-700 bg-gray-200 rounded border-0 shadow-inner'
            }
        });

        this.cardElement.mount('#card_element');
    },
    methods: {
        async processPayment ()
        {
            // send Payment information to laravel and strip
            this.paymentProcessing = true;

            const {paymentMethod, error} = await this.stripe.createPaymentMethod(
                'card', this.cardElement, {
                    billing_details: {
                        name: this.form.name,
                        email: this.form.email,
                        address: {
                            line1: this.form.address,
                            city: this.form.city,
                            state: this.form.state,
                            postal_code: this.form.zip_code,
                            // country: this.form.country,
                        }
                    }
                }
            );
            if (error)
            {
                this.paymentProcessing = false;
                console.log(error);
            }
            else
            {
                this.form.payment_method_id = paymentMethod.id;
                this.form.amount = 350;
                this.form.post(route('processPayment'), {
                    errorBag: 'default',
                    preserveScroll: true,
                    onSuccess: () => this.form.reset(),
                    onError: () => {
                        this.paymentProcessing = false;
                    }
                });
            }
        }
    }
}
</script>

<style scoped>

</style>
