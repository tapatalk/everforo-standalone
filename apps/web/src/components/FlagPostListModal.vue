<template>
    <FullScreenModal
        :title="title"
        v-on:close="onCancel"
        :content-max-width="isMobile?'95%':'520px'"
        :content-height="isMobile ? '60%' : '50%'"
        :max-height="isMobile ? '60%' : '50%'"
        :top="isMobile ? '20%' : '25%'"
    >
        <template v-slot:header>
            <div
                v-if="title"
                class="title"
            >{{title}}</div>
        </template>
        <div
            v-on:click="onCancel"
            :class="['modal-close-btn', {'mobile': isMobile}]"
        >
            <Icons
                type="chacha"
            />
        </div>

        <section
            v-if="flagList.length"
        >
            <div
                v-for="flag in flagList"
                :key="flag.id"
            >
                <ProfileListItem
                    :profile="flag.user"
                    :content="flag.reason_msg"
                    :is-ban="$store.getters['BanUser/isBan'](flag.user.id, flag.is_ban)"
                    :online="flag.online"
                />
            </div>
        </section>
        <section
            v-else
        >
            <a-skeleton avatar :paragraph="{rows: 1}" />
        </section>
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {FLAG_REASON_MAPPING} from '@/helpers/Utils';
    import {FlagInterface} from '@/helpers/Interfaces';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import ProfileListItem from '@/components/ProfileListItem.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            FullScreenModal,
            ProfileListItem,
        },
    })
    export default class FlagPostListModal extends Vue {

        private reasonMapping = FLAG_REASON_MAPPING;

        protected visible: boolean = true;

        protected title: string = '';

        protected flagList: FlagInterface[] = [];

        protected isMobile:boolean = IS_MOBILE;

        get flagPostListId(): number {
            return this.$store.state.Flag.flagPostListId;
        }

        protected created(): void {

            if (!this.flagPostListId) {
                return;
            }

            this.$store.dispatch('Flag/flagList', this.flagPostListId)
            .then((response: FlagInterface[]) => {
                this.flagList = response;

                const total = this.flagList.length;
                this.title = this.$tc('flag_post_list_title', total, {n: total});

                for (let i = 0; i < this.flagList.length; i++) {

                    if (this.flagList[i].reason)
                    {
                        for (let j = 0; j < FLAG_REASON_MAPPING.length; j++) {
                            if (FLAG_REASON_MAPPING[j].value == this.flagList[i].reason){
                                this.flagList[i].reason_msg = this.$t('someone_flagged_post_as', 
                                {username: this.flagList[i].user!.name, reason: this.$t(FLAG_REASON_MAPPING[j].text)}) as string
                            }
                        }
                    }
                }
            });
        }

        protected onCancel() {
            this.$store.commit('Flag/setFlagPostListId', 0);
        }

    }
</script>