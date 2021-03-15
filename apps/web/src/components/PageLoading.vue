<template>
    <a-row 
        v-if="loading"
        class="height-100 page-loading-row" type="flex" justify="space-around" align="middle"
    >
        <a-col :span="24">
            <div class="height-100 loading-wrapper">
                <a-icon type="loading" />
            </div>
        </a-col>
    </a-row>
</template>
<script lang="ts">
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({
    components: {
    },
})
export default class PageLoading extends Vue {
    @Prop()
    protected loading!: boolean;
}
</script>
<style lang="scss" scoped>
    .page-loading-row {
        background-color: rgba(255, 255, 255, 0.8);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        text-align: center;
    }
</style>