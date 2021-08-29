<template>
    <div :class="group_class" class="input-group">
        <label>{{ label_text }}</label>
        <input :class="{'bg-white' : disable}" :type="type"
               :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
        <p v-if="error != ''">{{error}}</p>
    </div>
</template>

<script>
export default {
    name: "LabeledInput",
    props: {
        group_class: String,
        input_name: String,
        label_text: String,
        disable: Boolean,
        modelValue: String,
        type : {
            type: String,
            default: 'text'
        },
        error : {
            type: String,
            default: ''
        }
    },
}
</script>
