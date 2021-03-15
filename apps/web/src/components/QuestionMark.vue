<template>
    <img
        class='question-mark'
        src="/img/question-mark.png" 
        alt=""
    >
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';

    @Component({
        components: {
        }
    })
    export default class QuestionMark extends Vue {
    }
</script>
<style lang="scss" scoped>
    .question-mark {
        width: 80px;
    }
</style>
