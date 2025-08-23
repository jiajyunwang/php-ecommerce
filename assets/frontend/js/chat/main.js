export const chatIcon = document.querySelector(".chat-icon");
export const chatBox = document.querySelector(".chat-box");
const closeChat = document.getElementById("close-chat");
const sendMessage = document.getElementById("send-message");
const messageInput = document.getElementById("message-input");
const contacts = document.getElementById("contacts");
const contentHeader = document.querySelector(".chat-content-header");
const unreadTotal = document.querySelector(".unread-total");
export const messages = document.getElementById("messages");
const unreadCount = document.querySelector(".unread-count");

export let activeContact = undefined;
let lastMessageDate = undefined;
export let roomId = undefined;
export let userId = undefined;
export let chatNickname = undefined;
let unreadTotalCount = 0;
let isOpenChat = false;
let isListen = undefined;
let socket = undefined;
export let role = undefined;

// if ($('meta[name="access-token"]') !== null){
//     const accessToken = $('meta[name="access-token"]').attr('content');
//     Object.assign(window.Echo.connector.options.auth.headers, {
//         Authorization: 'Bearer ' + accessToken,
//     });
// }

closeChat.addEventListener("click", () => {
    chatBox.style.display = "none";
    chatIcon.style.display = "flex";
    isOpenChat = false;
});

// const userId = document.cookie.match(/PHPSESSID=([^;]+)/)[1];

contacts.addEventListener("click", (e) => {
    if (e.target.tagName === "LI") {
        if (e.target != activeContact){
            $('.chat-input').hide();
            $('.chat-content-header').hide();

            if (activeContact) {
                activeContact.classList.remove("active");
            }
            activeContact = e.target;
            activeContact.classList.add("active");

            messages.innerHTML = "";
            roomId = activeContact.dataset.roomId;
            userId = activeContact.dataset.userId;
            chatNickname = activeContact.dataset.name;
            adminFetchMessages(roomId, chatNickname, activeContact);
            isListen = activeContact.dataset.isListen;
            if (isListen === 'false'){
                role = 'user';
                messageListen(role);
            }
        }
    }
});

export function setRole() {
    role = 'admin';
}

export function messageListen(senderRole) {
    socket = io('http://socket-env.eba-k6f2cxcp.us-east-1.elasticbeanstalk.com:3120', {
        query: {
            session_id: userId
        }
    });

    if (activeContact) {
        activeContact.dataset.isListen = 'true';
    }

    socket.on(userId, function(msg) {
        if (msg.role === senderRole) {
            let data = msg.message;
            if (msg.userId === userId && isOpenChat === true) {
                if (lastMessageDate !== data.date) {
                    addDateLabel(data.date);
                    lastMessageDate = data.date;
                }
                addMessage("remote", data.message, data.time);
                messages.scrollTop = messages.scrollHeight;
                markMessageAsRead(msg.messageId);
            }
        }
    });
}

export function setUserId() {
    userId = chatBox.dataset.userId;
}

export function setChatState() {
    isOpenChat = true;
}

async function markMessageAsRead(messageId) {
    try {
        const response = await fetch('/user/chat/mark-as-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({ 
                messageId: messageId 
            })
        });

        const data = await response.text();

    } catch (error) {
        console.error("Error mark messages as read:", error);
    }
}

export async function fetchChatList() {
    try {
        const response = await fetch(`/chat/room-list`);
        const data = await response.json();
        contacts.innerHTML = '';
        unreadTotalCount = 0;
        data.forEach((room) => {
            const chatListElement = document.createElement("li");
            chatListElement.dataset.roomId = room.roomId;
            chatListElement.dataset.userId = room.userId;
            chatListElement.dataset.name = room.nickname;
            chatListElement.dataset.isListen = 'false';

            const chatNicknameElement = document.createElement("div");
            chatNicknameElement.className = 'room-name';
            chatNicknameElement.textContent = room.nickname;

            const unreadElement = document.createElement("div");
            unreadElement.className = 'unread';

            const unreadCountElement = document.createElement("div");
            unreadCountElement.className = 'center unread-count';
            unreadCountElement.textContent = room.unreadCount;
            if (room.unreadCount !== 0) {
                unreadElement.style.display = 'block';
                unreadTotalCount += room.unreadCount; 
            } 

            chatListElement.appendChild(chatNicknameElement);
            unreadElement.appendChild(unreadCountElement);
            chatListElement.appendChild(unreadElement);
            contacts.appendChild(chatListElement);

        });
        if (unreadTotalCount !== 0) {
            unreadCount.innerHTML = unreadTotalCount;
            unreadTotal.style.display = 'block'; 
        }

    } catch (error) {
        console.error("Error fetching chat list:", error);
    }
}

export async function adminFetchMessages(roomId, chatNickname, activeContact) {
    try {
        const response = await fetch(`/admin/chat/messages?id=${roomId}`);
        const data = await response.json();

        messages.innerHTML = "";
        Object.entries(data).forEach(([date, messagesForDate]) => {
            addDateLabel(date);
            lastMessageDate = date;

            messagesForDate.forEach((message) => {
                const senderClass = message.sender_id === message.user_id ? "local" : "remote";
                addMessage(senderClass, message.message, message.time);
            });
        });
        $('.chat-input').show();
        $('.chat-content-header').show();
        contentHeader.textContent = chatNickname;
        const unread = activeContact.querySelector(".unread");
        const unreadCountElement = activeContact.querySelector(".unread-count");
        unreadTotalCount -= unreadCountElement.textContent;
        if (unreadTotalCount !== 0) {
            unreadCount.innerHTML = unreadTotalCount;
        } else {
            unreadTotal.style.display = "none";
        }
        unread.style.display = 'none';
        messages.scrollTop = messages.scrollHeight;
    } catch (error) {
        console.error("Error fetching messages:", error);
    }
}

export async function fetchMessages() {
    try {
        const response = await fetch(`/user/chat/messages`);
        const data = await response.json();

        messages.innerHTML = ""; 
        Object.entries(data).forEach(([date, messagesForDate]) => {
            addDateLabel(date);
            lastMessageDate = date;

            messagesForDate.forEach((message) => {
                const senderClass = message.sender_id === message.user_id ? "local" : "remote";
                addMessage(senderClass, message.message, message.time);
            });
        });
        messages.scrollTop = messages.scrollHeight;
    } catch (error) {
        console.error("Error fetching messages:", error);
    }
}

function addDateLabel(date) {
    const dateLabel = document.createElement("div");
    dateLabel.className = "date-label";
    dateLabel.textContent = date;
    messages.appendChild(dateLabel);
}

function addMessage(sender, text, time) {
    const messageWrapper = document.createElement("div");
    messageWrapper.className = `message-wrapper ${sender}`;

    const messageElement = document.createElement("div");
    messageElement.className = `message ${sender}`;
    messageElement.textContent = text;

    const timeElement = document.createElement("span");
    timeElement.className = "message-time";
    timeElement.textContent = time;

    if(sender=='local') {
        messageWrapper.appendChild(timeElement);
        messageWrapper.appendChild(messageElement);
    } else {
        messageWrapper.appendChild(messageElement);
        messageWrapper.appendChild(timeElement);
    }
    messages.appendChild(messageWrapper);
}

async function sendMessageToServer(text, roomId) {
    try {
        const response = await fetch('/user/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            body: JSON.stringify({
                message: text,
                roomId: roomId,
            }),
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Server error: ${errorText}`);
        }
        
        const data = await response.json();
        if (role === 'user') {
            socket.emit(userId, {
                message: data, 
                userId: userId, 
                messageId: data.messageId, 
                role: 'admin'
            });
        } else if (role === 'admin')
            socket.emit(userId, {
                message: data, 
                userId: userId, 
                messageId: data.messageId, 
                role: 'user'
            });


        if (lastMessageDate !== data.date) {
            addDateLabel(data.date);
            lastMessageDate = data.date;
        }
        addMessage("local", data.message, data.time);
        messages.scrollTop = messages.scrollHeight;
    } catch (error) {
        console.error("Error sending message:", error);
    }
}

export async function fetchUnreadCount() {
    try {
        const response = await fetch(`/user/chat/unread`);
        const data = await response.json();

        unreadTotalCount = data;

        if (unreadTotalCount !== 0) {
            unreadCount.innerHTML = unreadTotalCount;
            unreadTotal.style.display = 'block'; 
        }
        
    } catch (error) {
        console.error("Error fetching unread messages count:", error);
    }
}

messageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
        sendMessage.click();
    }
});
sendMessage.addEventListener("click", () => {
    const text = messageInput.value.trim();
    if (text === "") return;
    sendMessageToServer(text, roomId);
    messageInput.value = ""; 
});
