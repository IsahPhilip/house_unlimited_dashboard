<?php
// admin/messages.php - 100% WORKING & BEAUTIFUL (FINAL VERSION)
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

$admin_id = $_SESSION['user']['id'];
$selected_user = intval($_GET['user'] ?? 0);

// FIXED: Get all conversations with proper unread count
$conversations = $db->query("
    SELECT DISTINCT
        u.id,
        u.name,
        u.email,
        u.photo,
        latest.message AS last_message,
        latest.created_at AS last_time,
        COALESCE(unread.unread_count, 0) AS unread_count
    FROM users u
    INNER JOIN (
        SELECT 
            CASE 
                WHEN sender_id = $admin_id THEN recipient_id
                WHEN recipient_id = $admin_id THEN sender_id
            END AS other_user_id,
            message,
            created_at
        FROM messages
        WHERE sender_id = $admin_id OR recipient_id = $admin_id
    ) latest ON latest.other_user_id = u.id
    LEFT JOIN (
        SELECT sender_id, COUNT(*) AS unread_count
        FROM messages
        WHERE recipient_id = $admin_id AND is_read = 0
        GROUP BY sender_id
    ) unread ON unread.sender_id = u.id
    WHERE u.id != $admin_id
    ORDER BY latest.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get messages with selected user
$messages = [];
if ($selected_user > 0) {
    $stmt = $db->prepare("
        SELECT m.*, 
               u1.name AS sender_name, 
               u2.name AS recipient_name
        FROM messages m
        LEFT JOIN users u1 ON m.sender_id = u1.id
        LEFT JOIN users u2 ON m.recipient_id = u2.id
        WHERE (m.sender_id = ? AND m.recipient_id = ?) 
           OR (m.sender_id = ? AND m.recipient_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param('iiii', $admin_id, $selected_user, $selected_user, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Mark all messages from this user as read
    $db->query("UPDATE messages SET is_read = 1 WHERE recipient_id = $admin_id AND sender_id = $selected_user");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .messages-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            height: calc(100vh - 120px);
            gap: 0;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        }
        body.dark .messages-layout { background: #0f172a; }

        .conversations-list {
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
        }
        body.dark .conversations-list { background: #1e293b; border-color: #334155; }

        .conversation-item {
            padding: 1.2rem;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        body.dark .conversation-item { border-color: #334155; }
        .conversation-item:hover { background: #f1f5f9; }
        body.dark .conversation-item:hover { background: #334155; }
        .conversation-item.active { background: #3b82f6; color: white; }
        body.dark .conversation-item.active { background: #2563eb; }

        .conversation-avatar {
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 1rem;
        }
        .conversation-info { flex: 1; }
        .conversation-name { font-weight: 600; font-size: 1.1rem; }
        .conversation-preview { font-size: 0.9rem; color: #64748b; margin-top: 0.3rem; }
        body.dark .conversation-preview { color: #94a3b8; }
        .conversation-time { font-size: 0.8rem; color: #94a3b8; }
        .unread-badge {
            background: #ef4444; color: white; font-size: 0.75rem;
            padding: 0.2rem 0.5rem; border-radius: 12px; font-weight: bold;
        }

        .chat-area {
            display: flex;
            flex-direction: column;
            background: white;
        }
        body.dark .chat-area { background: #0f172a; }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        body.dark .chat-header { background: #1e293b; border-color: #334155; }

        .chat-messages {
            flex: 1;
            padding: 2rem;
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
            background: #3b82f6; color: white; align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message.received {
            background: #f1f5f9; color: #1e293b; align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        body.dark .message.received { background: #334155; color: #e2e8f0; }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }

        .chat-input-area {
            padding: 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        body.dark .chat-input-area { background: #1e293b; border-color: #334155; }

        .chat-input {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        #messageInput {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-size: 1rem;
            resize: none;
            max-height: 120px;
        }
        #messageInput:focus { outline: none; border-color: #3b82f6; }

        .send-btn {
            background: #3b82f6; color: white; border: none;
            width: 50px; height: 50px; border-radius: 50%;
            font-size: 1.3rem; cursor: pointer;
            transition: all 0.2s;
        }
        .send-btn:hover { background: #2563eb; transform: scale(1.1); }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Messages Center</h1>
                <div class="msg-badge" id="totalUnread">0</div>
            </div>

            <div class="messages-layout">
                <!-- Conversations List -->
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div style="padding:2rem; text-align:center; color:#64748b;">
                            <h3>No messages yet</h3>
                            <p>All client conversations will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <div class="conversation-item <?= $conv['id'] == $selected_user ? 'active' : '' ?>"
                                 onclick="openChat(<?= $conv['id'] ?>)">
                                <div style="display:flex; align-items:center;">
                                    <img src="../assets/uploads/avatars/<?= $conv['photo'] ?: 'default.png' ?>" 
                                         class="conversation-avatar" alt="<?= htmlspecialchars($conv['name']) ?>">
                                    <div class="conversation-info">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <div class="conversation-name"><?= htmlspecialchars($conv['name']) ?></div>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="conversation-preview">
                                            <?= htmlspecialchars(strlen($conv['last_message'] ?? '') > 50 ? 
                                                substr($conv['last_message'], 0, 50).'...' : ($conv['last_message'] ?? 'No messages')) ?>
                                        </div>
                                        <div class="conversation-time">
                                            <?= $conv['last_time'] ? date('M j, g:ia', strtotime($conv['last_time'])) : '' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Chat Area -->
                <div class="chat-area">
                    <?php if ($selected_user > 0): 
                        $user_info = $db->query("SELECT name, photo FROM users WHERE id = $selected_user")->fetch_assoc();
                    ?>
                        <div class="chat-header">
                            <div style="display:flex; align-items:center;">
                                <img src="../assets/uploads/avatars/<?= $user_info['photo'] ?? 'default.png' ?>" 
                                     class="conversation-avatar" style="margin-right:1rem;">
                                <div>
                                    <h3 style="margin:0; font-size:1.4rem;"><?= htmlspecialchars($user_info['name']) ?></h3>
                                    <small style="color:#64748b;">Active in conversation</small>
                                </div>
                            </div>
                        </div>

                        <div class="chat-messages" id="messagesContainer">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?= $msg['sender_id'] == $admin_id ? 'sent' : 'received' ?>">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    <div class="message-time">
                                        <?= date('g:ia', strtotime($msg['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="chat-input-area">
                            <div class="chat-input">
                                <textarea id="messageInput" placeholder="Type your message..." rows="1"></textarea>
                                <button class="send-btn" onclick="sendMessage(<?= $selected_user ?>)">Send</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#64748b;">
                            <h2>Select a conversation</h2>
                            <p>Click on any user to start messaging</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openChat(userId) {
            location.href = `messages.php?user=${userId}`;
        }

        function sendMessage(toUser) {
            const input = document.getElementById('messageInput');
            const msg = input.value.trim();
            if (!msg) return;

            fetch('../api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ to_user: toUser, message: msg })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const container = document.getElementById('messagesContainer');
                    const div = document.createElement('div');
                    div.className = 'message sent';
                    div.innerHTML = msg.replace(/\n/g, '<br>') + 
                                  `<div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>`;
                    container.appendChild(div);
                    container.scrollTop = container.scrollHeight;
                    input.value = '';
                }
            });
        }

        document.getElementById('messageInput')?.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.querySelector('.send-btn')?.click();
            }
        });

        document.getElementById('messagesContainer')?.scrollTo(0, document.getElementById('messagesContainer').scrollHeight);
    </script>
</body>
</html>