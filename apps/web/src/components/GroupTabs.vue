<template>
    <a-tabs
        :default-active-key="defaultKey"
        :animated="false"
        v-on:change="switchTab"
    >
        <a-tab-pane
            key="1"
            :tab="$t('general_settings')"
        >
            <slot name="tab1"></slot>
        </a-tab-pane>
        <a-tab-pane
            key="2"
            :tab="$t('extensions')"
        >
            <slot name="tab2"></slot>
        </a-tab-pane>
        <a-tab-pane
            v-if="isAirdropEnabled"
            key="3"
            :tab="$t('airdrop_tab')"
        >
            <slot name="tab3"></slot>
        </a-tab-pane>
    </a-tabs>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';

    @Component({
        components: {
            
        },
    })
    export default class GroupTabs extends Vue {
        @Prop()
        public defaultKey!: string;

        protected created() {
            // when user selected another tab,
            // then closed the popup
            // when open again, we need to set it to 1
        }

        get isAirdropEnabled(): number {
            return 0; // disable token related feature 
            // return this.$store.getters['GroupExtensions/getAirdropStatus'];
        }

        // get isAttachmentsEnabled(): number {
        //     return this.$store.getters['GroupExtensions/getAttachmentsStatus'];
        // }


        protected switchTab(activeKey: number) {
            this.$store.commit('setActivatedGroupTab', activeKey);
        }
    }
</script>
<style lang="scss" scoped>

</style>
