<template>
    <div class="chat-widget-container">
        <!-- Floating Button -->
        <button class="chat-float-btn shadow-lg" @click="toggleChat">
            <i class="fa fa-comments"></i>
            <span v-if="unreadCount > 0" class="badge badge-danger unread-badge">{{ unreadCount }}</span>
        </button>

        <!-- Chat Window -->
        <transition name="slide-up">
            <div v-if="isOpen" class="chat-window shadow-lg">
                <div class="chat-header">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-comments mr-2"></i>
                        <span class="font-weight-bold">Chat</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <button v-if="isAdmin" class="btn btn-sm btn-danger text-white p-1 mr-3" @click="deleteChat" title="Borrar todo el chat">
                            <i class="fa fa-trash"></i>
                        </button>
                        <span class="mr-2" style="font-size: 0.9em;">{{ currentUser }}</span>
                        <button class="btn btn-sm btn-link text-white p-0 mr-3" @click="resetName" title="Cambiar Nombre">
                            <i class="fa fa-user"></i>
                        </button>
                        <button class="close-btn" @click="toggleChat" style="font-size: 24px; line-height: 20px;">&times;</button>
                    </div>
                </div>
                
                <div class="chat-body" ref="chatBody">
                    <div v-for="(msg, index) in messages" :key="index" 
                         class="d-flex flex-column"
                         :class="{'align-items-end': msg.usuario === currentUser, 'align-items-start': msg.usuario !== currentUser}">
                        
                        <div class="message-bubble shadow-sm"
                            :class="{'my-msg': msg.usuario === currentUser, 'other-msg': msg.usuario !== currentUser}">
                            
                            <div class="msg-sender text-primary mb-1" v-if="msg.usuario !== currentUser">
                                <strong>{{ msg.usuario }}</strong>
                            </div>
                            
                            <div class="msg-text">{{ msg.contenido }}</div>
                            <div class="msg-time d-flex align-items-center justify-content-end">
                                {{ formatTime(msg.created_at) }}
                                <span v-if="msg.usuario === currentUser" class="ml-1">
                                    <i class="fa fa-check" v-if="!msg.leido" style="color: #999;"></i>
                                    <i class="fa fa-check-double" v-else style="color: #34b7f1;"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-footer">
                    <div class="input-group">
                        <input type="text" 
                               v-model="newMessage" 
                               @keyup.enter="sendMessage"
                               class="form-control" 
                               placeholder="Escribe un mensaje...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" @click="sendMessage">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: ['usuario', 'idrol'],
        data() {
            return {
                isOpen: false,
                messages: [],
                newMessage: '',
                currentUser: '',
                polling: null,
                lastMsgId: 0,
                unreadCount: 0,
                isFirstLoad: true
            }
        },
        computed: {
            isAdmin() {
                return this.idrol === 'Administrador';
            }
        },
        mounted() {
            this.identifyUser();
            this.fetchMessages();
            this.startPolling();
        },
        beforeDestroy() {
            clearInterval(this.polling);
        },
        watch: {
            // When opening, clear unread and scroll, and mark as read
            isOpen(val) {
                if (val) {
                    this.unreadCount = 0;
                    this.markMessagesAsRead();
                    this.$nextTick(() => this.scrollToBottom());
                }
            }
        },
        methods: {
            // ... (existing methods)
            deleteChat() {
                if (!confirm("¿Estás seguro de que deseas borrar todo el historial del chat? esta acción no se puede deshacer.")) {
                    return;
                }
                axios.delete('/mensaje/borrarTodo')
                    .then(response => {
                        this.messages = [];
                        this.lastMsgId = 0;
                        alert("Chat borrado exitosamente.");
                    })
                    .catch(error => {
                        console.error(error);
                        alert("Error al borrar el chat.");
                    });
            },
            identifyUser() {
                // Try to get from localStorage first (persists manual changes)
                let storedName = localStorage.getItem('chat_username');
                
                if (storedName) {
                    this.currentUser = storedName;
                } else if (this.usuario && this.usuario !== 'Invitado') {
                    // If no stored name, use the prop from Auth as default
                    this.currentUser = this.usuario;
                    localStorage.setItem('chat_username', this.usuario);
                } else {
                    // Prompt for name as last resort
                    let name = '';
                    while (!name) {
                        name = prompt("Por favor, ingresa tu nombre para el chat:", "Usuario " + Math.floor(Math.random() * 1000));
                    }
                    this.currentUser = name;
                    localStorage.setItem('chat_username', name);
                }
            },
            toggleChat() {
                this.isOpen = !this.isOpen;
            },
            formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },
            scrollToBottom() {
                const container = this.$refs.chatBody;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },
            startPolling() {
                this.polling = setInterval(() => {
                    this.fetchMessages();
                }, 3000);
            },
            fetchMessages() {
                axios.get('/mensaje/listar').then(response => {
                    const newMessages = response.data;
                    
                    // Mark as read if window is open
                    if (this.isOpen && newMessages.length > 0) {
                         const hasUnread = newMessages.some(m => !m.leido && m.usuario !== this.currentUser);
                         if(hasUnread) this.markMessagesAsRead();
                    }

                    // Check for new messages since last check
                    if (newMessages.length > 0) {
                        const last = newMessages[newMessages.length - 1];
                        
                        // Simply update the list to reflect status changes (read ticks)
                        // If it's just a status update, we replace the array.
                        // If new ID, we handle notifications.
                        
                        if (last.id > this.lastMsgId) {
                            if (!this.isOpen && !this.isFirstLoad && last.usuario !== this.currentUser) {
                                this.unreadCount++;
                                this.playNotificationSound();
                            }
                            this.lastMsgId = last.id;
                            
                            if (this.isOpen) {
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        }
                        this.messages = newMessages;
                    } else {
                        // If empty (deleted), clear local array
                         this.messages = [];
                         this.lastMsgId = 0;
                    }
                    this.isFirstLoad = false;
                }).catch(error => console.error(error));
            },
            playNotificationSound() {
                try {
                    const audio = new Audio('http://soundbible.com/grab.php?id=1446&type=mp3'); // Simple beep
                    audio.play().catch(e => console.log('Audio play failed', e));
                } catch (e) {
                    console.error(e);
                }
            },
            markMessagesAsRead() {
                axios.post('/mensaje/marcar', { usuario: this.currentUser })
                     .then(() => {
                         // Successfully marked
                     })
                     .catch(err => console.error(err));
            },
            sendMessage() {
                if (!this.newMessage.trim()) return;

                const msg = {
                    usuario: this.currentUser,
                    contenido: this.newMessage
                };

                axios.post('/mensaje/enviar', msg).then(response => {
                    this.newMessage = '';
                    this.fetchMessages(); // Refresh immediately
                }).catch(error => console.error(error));
            },
            resetName() {
                localStorage.removeItem('chat_username');
                // Force prompt again
                let name = '';
                while (!name) {
                    name = prompt("Cambiar nombre a:", "Usuario " + Math.floor(Math.random() * 1000));
                }
                this.currentUser = name;
                localStorage.setItem('chat_username', name);
                this.fetchMessages();
            }
        }
    }
</script>

<style scoped>
    .chat-widget-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .chat-float-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #1985ac; /* App primary color */
        color: white;
        border: none;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
        position: relative;
    }

    .chat-float-btn:hover {
        transform: scale(1.1);
        background-color: #115f7a;
    }

    .unread-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ff4757;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }

    .chat-window {
        position: absolute;
        bottom: 80px; /* Above button */
        right: 0;
        width: 350px;
        height: 500px;
        background-color: white;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e1e4e8;
    }

    .chat-header {
        background-color: #1985ac;
        color: white;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .close-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }

    .chat-body {
        flex: 1;
        background-color: #f7f9fc;
        padding: 15px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message-bubble {
        max-width: 80%;
        padding: 8px 12px;
        border-radius: 7.5px;
        position: relative;
        font-size: 14.2px;
        line-height: 19px;
        margin-bottom: 4px;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }

    .my-msg {
        align-self: flex-end;
        background-color: #dcf8c6; /* WhatsApp Green */
        color: #000;
        border-top-right-radius: 0;
    }
    
    .my-msg::after {
        content: '';
        position: absolute;
        top: 0;
        right: -8px; 
        width: 0;
        height: 0;
        border-top: 10px solid #dcf8c6;
        border-right: 10px solid transparent;
    }

    .other-msg {
        align-self: flex-start;
        background-color: #ffffff;
        color: #000;
        border-top-left-radius: 0;
    }
    
    .other-msg::after {
        content: '';
        position: absolute;
        top: 0;
        left: -8px; 
        width: 0;
        height: 0;
        border-top: 10px solid #ffffff;
        border-left: 10px solid transparent;
        transform: scaleX(-1); /* Correct orientation for left tail */
    }

    .msg-sender {
        font-size: 10px;
        font-weight: bold;
        color: #666;
        margin-bottom: 2px;
    }

    .msg-time {
        font-size: 9px;
        color: rgba(0,0,0,0.5);
        text-align: right;
        margin-top: 4px;
    }

    .chat-footer {
        padding: 12px;
        border-top: 1px solid #e1e4e8;
        background-color: white;
    }

    /* Animation */
    .slide-up-enter-active, .slide-up-leave-active {
        transition: all 0.3s ease;
    }
    .slide-up-enter, .slide-up-leave-to {
        transform: translateY(20px);
        opacity: 0;
    }

    /* Mobile Responsive Logic */
    @media (max-width: 576px) {
        .chat-container {
            right: 10px;
            bottom: 10px;
        }
        
        .chat-window {
            width: calc(100vw - 20px);
            height: 70vh; /* Don't cover everything, allow seeing behind */
            bottom: 70px;
            right: 0px;
            max-width: 400px;
        }

        .chat-toggle-btn {
            width: 55px;
            height: 55px;
            font-size: 24px;
        }

        .message-bubble {
            max-width: 90%;
            font-size: 15px; /* Better readability on touch */
        }
        
        .chat-header {
            padding: 10px 15px;
        }

        .chat-footer {
            padding: 8px;
        }
    }
</style>
