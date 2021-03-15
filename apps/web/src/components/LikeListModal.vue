<template>
    <FullScreenModal
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
            v-if="likeList.length"
        >
            <div
                v-for="like in likeList"
                :key="like.id"
            >
                <ProfileListItem
                    v-if="like.user"
                    :profile="like.user"
                    :is-ban="$store.getters['BanUser/isBan'](like.user.id, like.is_ban)"
                    :online="like.online"
                >
                    <template v-slot:content>
                        <Username
                            v-if="like.user"
                            :username="like.user.name"
                            :profile-id="like.user.id"
                        />
                    </template>
                </ProfileListItem>
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
    import {LikeInterface} from '@/helpers/Interfaces';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import ProfileListItem from '@/components/ProfileListItem.vue';
    import Username from '@/components/Username.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            FullScreenModal,
            ProfileListItem,
            Username,
        },
    })
    export default class LikeListModal extends Vue {

        protected visible: boolean = true;

        protected title: string = '';

        protected likeList: LikeInterface[] = [];

        protected isMobile:boolean = IS_MOBILE;

        get postId(): number {
            return this.$store.state.Like.likeListPostId;
        }

        protected created(): void {

            if (!this.postId) {
                return;
            }

            this.$store.dispatch('Like/likeList', this.postId)
            .then((response: LikeInterface[]) => {
                this.likeList = response;

                const total = this.likeList.length;
                this.title = this.$tc('like_list_title', total, {n: total});
            });
        }

        protected onCancel() {
            this.$store.commit('Like/setLikeListPostId', 0);
        }
        
    }
</script>