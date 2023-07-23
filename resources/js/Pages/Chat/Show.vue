<template>
    <div class="w-3/4 mx-auto">
        <div class="border-t-2 p-1 flex items-center">
            <div v-if="chat">
                <div v-for="user in users">
                    <div v-if="user.id != this.$page.props.auth.user.id" class="flex items-center">
                        <p class="text-lg">{{ user.name }}</p>
                        <input class="ml-5 rounded-full text-sm" type="text" v-on:change="scrollToItems = []" v-model="searchText">
                        <div>
                            <a @click="scrollToItem()" class="text-sm ml-2 p-2 inline-block bg-slate-500 text-white rounded"
                                href="#">{{ this.scrollToItems.length > 1 ? "Далее" : "Поиск" }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div v-if="messages" class="bg-slate-200 overflow-y-auto h-96" ref="scrollContainer"
                v-bind:scrollTop="scrollContainerHeight">
                <div v-for="message in messages" key="message.id" :id="`message-${message.id}`" class="w-full">
                    <div :class="['px-5 py-2 rounded m-3', message.content == searchText ? 'bg-slate-300' : '',
                        message.is_owner ? 'text-right' : 'text-left']">
                        <p class="">{{ message.user_name }}</p>
                        <div class="inline-block bg-white px-5 py-2 rounded">
                            <p class="mr-3 text-lg">{{ message.content }}</p>
                        </div>
                        <p class="text-xs italic text-slate-400">{{ message.time }}</p>
                    </div>
                </div>
            </div>
            <div class="m-5 rounded p-5 flex items-center justify-center">
                <div>
                    <input placeholder="Введите..." class="rounded-full h-12 w-96 ml-5 text-lg border-1 my-5" type="text"
                        v-model="content">
                </div>
                <div>
                    <a @click.prevent="store" class=" ml-4 p-2 inline-block bg-slate-500 text-white rounded"
                        href="#">Отправить</a>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
import Main from "@/Layouts/Main.vue"
export default {
    name: "Show",
    props: [
        'chat',
        'users',
        'messages',
    ],
    layout: Main,
    data() {
        return {
            content: '',
            scrollContainerHeight: 0,
            searchText: '',
            scrollToItems: [],
            findedMessageId: ''
        }
    },
    computed: {
        userIds() {
            return this.users.map(user => {
                return user.id
            }).filter(userId => {
                return userId != this.$page.props.auth.user.id
            })
        }
    },
    mounted() {
        this.scrollToBottom()
    },
    methods: {
        scrollToItem() {
            if (this.scrollToItems.length == 0){
            this.messages.map(message => {
                if (message.content == this.searchText) {
                    this.scrollToItems.push(message.id);
                };
            }) }else this.scrollToItems.pop();
            setTimeout(() => {
                const element = document.getElementById(`message-${this.scrollToItems.at(-1)}`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }, 0);
        },
        store() {
            axios.post('/messages', {
                chat_id: this.chat.id,
                content: this.content,
                user_ids: this.userIds,
            })
                .then(res => {
                    this.messages.push(res.data);
                })
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const scrollContainer = this.$refs.scrollContainer;
                this.scrollContainerHeight = scrollContainer.scrollHeight;
            });
        }
    }
}
</script>