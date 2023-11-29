<template>
    <div class="grid grid-cols-4 px-32 gap-x-2">
        <div class="col-span-1 flex flex-col">
            <div class="flex mb-4">
                        <input class="ml-5 rounded-full text-sm" type="text" v-on:change="scrollToItems = []" v-model="searchText">
                        <div>
                            <a @click="scrollToItem()" class="text-sm ml-2 p-2 inline-block bg-cyan-500 text-white rounded-full"
                                href="#">{{ this.scrollToItems.length > 1 ? "Далее" : "Поиск" }}</a>
                        </div>
                </div>
                <div class="text-xl">
                        Ваши чаты:
                    </div>
            <div class="flex flex-col overflow-auto h-96">
                <div v-if="users">
                    <div v-for="user in all_users"
                        class="flex items-center hover:cursor-pointer hover:bg-cyan-200 border-b-2 p-3"
                        @click.prevent="choose(user.id)">
                        <div class="h-12 w-12 bg-cyan-600 rounded-full">
                        </div>
                        <div class="flex flex-col ml-3">
                            <p class="">{{ user.name }}</p>
                            <div v-for="friend in myFriends">
                                <div v-if="friend.user_id === user.id" class="text-xs">
                                    <div v-for="message in latest_messages">
                                        <div v-if="message.chat_id === friend.chat_id">
                                            {{ message.content }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

        </div>
        <div class="col-span-3 min-h-96">
            <div class="p-1 flex items-center bg-gray-600 rounded-t-xl py-4">
                <div v-if="chat">
                    <div v-for="user in users">
                        <div v-if="user.id != this.$page.props.auth.user.id" class="flex items-center ml-8 mt-4">
                            <div class="w-12 h-12 bg-cyan-500 rounded-full">
                            </div>
                            <p class="text-3xl ml-6 text-white">{{ user.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                
                <div v-if="messages" class="bg-cyan-200 overflow-y-auto h-96" ref="scrollContainer"
                    v-bind:scrollTop="scrollContainerHeight">
                    <div v-for="message in messages" key="message.id" :id="`message-${message.id}`" class="w-full">
                        <div :class="['px-5 py-2 rounded m-3', message.content == searchText ? 'bg-cyan-300' : '',
                            message.is_owner ? 'text-right' : 'text-left']">
                            <p :class="['message.is_owner ? mr-5 : ml-5']">{{ message.user_name }}</p>
                            <div class="inline-block bg-white px-5 py-2 rounded-md">
                                <div class="flex flex-col">
                                    <div class="mt-1" v-for="image in message.images">
    <img :src="image" class="w-36" alt="">
</div>
                                </div>
                                <p class="text-lg">{{ message.content }}</p>
                            </div>
                            <p class="text-xs italic text-cyan-400">{{ message.time }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="chat.id" class="m-5 rounded p-5 flex items-center justify-center">
                    <div class="hover:cursor-pointer bg-red-300 p-4 rounded-xl text-xl" ref="dropzone">
                        📎
                    </div>
                    <div>
                        <input placeholder="Введите..." class="rounded-full h-12 w-96 ml-5 text-lg border-1 my-5"
                            type="text" v-model="content">
                    </div>
                    <div>
                        <a @click.prevent="store" class="ml-4 p-2 inline-block bg-cyan-500 text-white rounded"
                            href="#">Отправить</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
import Main from "@/Layouts/Main.vue"
import Dropzone from "dropzone"
import { Axios } from 'axios';
export default {
    name: "Show",
    props: [
        'chat',
        'users',
        'messages',
        'all_users',
        'chats',
        'latest_messages',
        'auth_id'
    ],
    layout: Main,
    data() {
        return {
            content: '',
            scrollContainerHeight: 0,
            searchText: '',
            scrollToItems: [],
            findedMessageId: '',
            myFriends: this.chats,
            dropzone: null,
        }
    },
    computed: {
        userIds() {
            return this.users.map(user => {
                return user.id
            }).filter(userId => {
                return userId != this.$page.props.auth.user.id
            })
        },
    },
    beforeMount() {
        this.messages.forEach(message =>{
            message.images = message.images.split(', ');
        })
 },
    mounted() {
        this.scrollToBottom();
        this.dropzone = new Dropzone(this.$refs.dropzone, 
        { 
            url: "dffdgbhjg",
            autoProcessQueue: false,
            addRemoveLinks: false
        })
        let result = [];
        this.chats.forEach(chat => {
            const chatIds = chat.users.split('-').map(Number);
            let user_id = chatIds.filter(elem => elem !== this.auth_id)
            const obj = {
                user_id: user_id[0],
                chat_id: chat.id
            }
            result.push(obj)
        });
        this.myFriends = result;

    },
    methods: {
        choose(id) {
            this.$inertia.post('/chats', { title: null, users: [id] })
        },
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
            const data = new FormData()
            const files = this.dropzone.getAcceptedFiles()
            files.forEach(file => {
                data.append('images[]', file)
                this.dropzone.removeFile(file)
            })
            data.append('chat_id', this.chat.id)
            console.log(this.userIds);
            data.append('user_ids[]', this.userIds)
            data.append('content', this.content)
            console.log(data);
            axios.post ('/messages', data).then(res => {
                    this.content='',
                    this.messages.push(res.data);
                })
            //this.form.post('/messages')
            /*axios.post('/messages', {
                chat_id: this.chat.id,
                content: this.content,
                user_ids: this.userIds,
                image: this.image
            })
                .then(res => {
                    this.content=''
                    this.messages.push(res.data);
                })*/
        },
        scrollToBottom() {
            if (this.users.length > 0)
                this.$nextTick(() => {
                    const scrollContainer = this.$refs.scrollContainer;
                    this.scrollContainerHeight = scrollContainer.scrollHeight;
                });
        }
    }
}
</script>