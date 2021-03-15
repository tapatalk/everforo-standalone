<template>
    <span
        class="back"
        v-on:click="back"
    >
        <Icons type="fanhui"/>
    </span>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";

    @Component
    export default class Back extends Vue {
        @Prop()
        public route!: RawLocation;

        protected back() {
            if(this.route){
                this.$router.push(this.route);
            } else {
                this.$router.go(-1);
            }
        }
    }
</script>
<style lang="scss" scoped>
    .back {
        cursor: pointer;
        .ico {
            vertical-align:  -0.125em;
            color: #606878;
        }
    }
</style>
