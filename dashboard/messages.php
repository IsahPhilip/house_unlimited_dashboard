<?php
// dashboard/messages.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Messages • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .messages-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 0;
            height: calc(100vh - 140px);
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        body.dark .messages-layout { background: #1e1e1e; }

        .conversations-list {
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            background: #f8f9fc;
        }
        body.dark .conversations-list { background: #0f172a; border-color: #334155; }

        .conversation-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: 0.2s;
            position: relative;
        }
        body.dark .conversation-item { border-color: #334155; }
        .conversation-item:hover, .conversation-item.active {
            background: #e0e7ff;
        }
        body.dark .conversation-item:hover, body.dark .conversation-item.active {
            background: #1e293b;
        }

        .conversation-item.unread::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            background: #3b82f6;
            border-radius: 50%;
        }

        .conv-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
        }

        .conv-info h4 {
            margin: 0 0 0.4rem;
            font-size: 1.1rem;
            color: #1e293b;
        }
        body.dark .conv-info h4 { color: #e2e8f0; }

        .conv-last-msg {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .conv-time {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .chat-area {
            display: flex;
            flex-direction: column;
            background: white;
        }
        body.dark .chat-area { background: #121212; }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8f9fc;
        }
        body.dark .chat-header { background: #0f172a; border-color: #334155; }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 70%;
            padding: 1rem 1.2rem;
            border-radius: 18px;
            line-height: 1.5;
            position: relative;
        }
        .message.sent {
            align-self: flex-end;
            background: #3b82f6;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message.received {
            align-self: flex-start;
            background: #f1f5f9;
            color: #1e293b;
            border-bottom-left-radius: 4px;
        }
        body.dark .message.received { background: #1e293b; color: #e2e8f0; }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.4rem;
        }

        .message-input-area {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8f9fc;
            display: flex;
            gap: 1rem;
        }
        body.dark .message-input-area { background: #0f172a; border-color: #334155; }

        #messageInput {
            flex: 1;
            padding: 1rem 1.4rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            outline: none;
        }
        #messageInput:focus {
            border-color: #3b82f6;
        }

        #sendBtn {
            background: #3b82f6;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.3rem;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #64748b;
            font-size: 1.3rem;
        }
        .no-chat-selected img {
            width: 120px;
            opacity: 0.4;
            margin-bottom: 1rem;
        }

        .property-context {
            background: #f0f9ff;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            border-left: 4px solid #0ea5e9;
        }
        body.dark .property-context { background: #172554; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Messages</h1>
                <div>
                    <span id="onlineStatus" style="color:#10b981; font-weight:600;">● Online</span>
                </div>
            </div>

            <div class="messages-layout">
                <!-- Conversations List -->
                <div class="conversations-list" id="conversationsList">
                    <div style="padding:2rem; text-align:center; color:#64748b;">
                        Loading conversations...
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="chat-area">
                    <div id="noChat" class="no-chat-selected">
                        <img src="../assets/img/messages.svg" alt="Messages">
                        <p>Select a conversation to start messaging</p>
                        <small>Your messages are secure and private</small>
                    </div>

                    <div id="chatView" style="display:none; flex-direction:column; height:100%;">
                        <div class="chat-header" id="chatHeader">
                            <img id="chatAvatar" src="" class="conv-avatar">
                            <div>
                                <h4 id="chatName">Loading...</h4>
                                <small style="color:#10b981;">● Online</small>
                            </div>
                        </div>

                        <div id="propertyContext" class="property-context" style="margin:1rem 1.5rem; display:none;">
                            Discussing: <strong id="contextProperty"></strong>
                        </div>

                        <div class="chat-messages" id="chatMessages"></div>

                        <div class="message-input-area">
                            <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off" />
                            <button id="sendBtn">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        let activeChatWith = null;
        let activePropertyId = null;

        // Load Conversations
        async function loadConversations() {
            const res = await fetch('../api/get_conversations.php');
            const convos = await res.json();

            const list = document.getElementById('conversationsList');
            list.innerHTML = '';

            if (convos.length === 0) {
                list.innerHTML = `<div style="padding:3rem; text-align:center; color:#64748b;">
                    <img src="../assets/img/no-messages.svg" width="100"><br><br>
                    No messages yet.<br><small>Start by viewing a property and clicking "Chat Agent"</small>
                </div>`;
                return;
            }

            convos.forEach(c => {
                const item = document.createElement('div');
                item.className = `conversation-item ${c.unread ? 'unread' : ''}`;
                item.onclick = () => openChat(c.with_user_id, c.with_name, c.property_id, c.property_title);

                item.innerHTML = `
                    <div style="display:flex; align-items:center;">
                        <img src="../assets/uploads/avatars/${c.photo || 'default.png'}" class="conv-avatar">
                        <div class="conv-info" style="flex:1;">
                            <h4>${c.with_name}</h4>
                            <p class="conv-last-msg">${c.last_message || 'No messages yet'}</p>
                        </div>
                        <div class="conv-time">${c.last_time || ''}</div>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        // Open Chat
        function openChat(userId, name, propertyId = null, propertyTitle = null) {
            activeChatWith = userId;
            activePropertyId = propertyId;

            document.getElementById('noChat').style.display = 'none';
            document.getElementById('chatView').style.display = 'flex';
            document.getElementById('chatName').textContent = name;
            document.getElementById('chatAvatar').src = `../assets/uploads/avatars/${'default.png'}`;

            if (propertyId) {
                document.getElementById('propertyContext').style.display = 'block';
                document.getElementById('contextProperty').textContent = propertyTitle;
            } else {
                document.getElementById('propertyContext').style.display = 'none';
            }

            loadMessages();
            markAsRead();
        }

        // Load Messages
        async function loadMessages() {
            if (!activeChatWith) return;
            const res = await fetch(`../api/get_messages.php?with=${activeChatWith}`);
            const msgs = await res.json();

            const chat = document.getElementById('chatMessages');
            chat.innerHTML = '';

            msgs.forEach(m => {
                const msgDiv = document.createElement('div');
                msgDiv.className = `message ${m.from_user == <?= $user_id ?> ? 'sent' : 'received'}`;
                msgDiv.innerHTML = `
                    ${m.message}
                    <div class="message-time">${new Date(m.created_at).toLocaleTimeString('en-NG', {hour:'2-digit', minute:'2-digit'})}</div>
                `;
                chat.appendChild(msgDiv);
            });
            chat.scrollTop = chat.scrollHeight;
        }

        // Send Message
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const text = input.value.trim();
            if (!text || !activeChatWith) return;

            await fetch('../api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    to_user: activeChatWith,
                    message: text,
                    property_id: activePropertyId || null
                })
            });

            input.value = '';
            loadMessages();
            loadConversations(); // Refresh sidebar
        }

        // Mark as Read
        async function markAsRead() {
            if (!activeChatWith) return;
            await fetch('../api/mark_read.php', {
                method: 'POST',
                body: JSON.stringify({ from_user: activeChatWith })
            });
            updateUnreadBadge();
        }

        // Update Unread Badge
        function updateUnreadBadge() {
            fetch('../api/unread_count.php')
                .then(r => r.json())
                .then(d => {
                    const badge = document.querySelector('.msg-badge');
                    if (badge) badge.textContent = d.count > 0 ? d.count : '';
                });
        }

        // Event Listeners
        document.getElementById('sendBtn').onclick = sendMessage;
        document.getElementById('messageInput').addEventListener('keypress', e => {
            if (e.key === 'Enter') sendMessage();
        });

        // Auto Refresh
        loadConversations();
        updateUnreadBadge();
        setInterval(() => {
            loadConversations();
            if (activeChatWith) loadMessages();
            updateUnreadBadge();
        }, 8000);
    </script>
</body>
</html>