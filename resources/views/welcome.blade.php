<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Chat</title>
        <style>
            *{
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }
            #window-container{
                width: 350px;
                border: 2px solid #ddd;
                padding: 10px;
                margin: 0 auto;
                min-height: 500px;
                position: relative;
                overflow: hidden;
            }
            #chat-input-container{
                position: absolute;
                width: 100%;
                bottom: 10px;
            }
            #message{
                width: 80%;
            }
            #title{
                text-align: center;
            }
            #messages{
                display: block;
                height: 80%;
                margin: 0 auto;
                position: absolute;
                width: 100%;
                top: 40px;
                overflow: auto;
                list-style: none;
                padding-left: 0px;
            }
            .message{
                padding: 10px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                background: #ddd;
                margin: 5px;
                font-size: small;
                visibility: hidden;
                opacity: 0;
                -webkit-transition: visibility 0s, opacity 0.5s linear;
                -moz-transition: visibility 0s, opacity 0.5s linear;
                -ms-transition: visibility 0s, opacity 0.5s linear;
                -o-transition: visibility 0s, opacity 0.5s linear;
                transition: visibility 0s, opacity 0.5s linear;
                -ms-word-wrap: break-word;
                word-wrap: break-word;
            }
            .info{
                background: none;
            }
            .success{
                color: limegreen;
            }
            .error{
                color: red;
            }
            .sent-by-me{
                text-align:right;
                background: skyblue;
            }
            .message-visible{
                visibility: visible;
                opacity: 1;
            }
            #user-id-title{
                margin: 5px 0px 10px 0px;
            }
        </style>
    </head>
    <body>
        <h3 id="title">Anonymous Chat</h3>
        <div id="window-container">
            <h5 id="user-id-title">User ID : <span id="user-id">0</span></h5>
            <ul id="messages">
            </ul>
            <div id="chat-input-container">
                <input id="message" type="text" name="message" /> <button id="send-btn">Send</button>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                function hideScrollbar(){
                    var parent = document.getElementById('window-container');
                    var child = document.getElementById('messages');
                    child.style.paddingRight = child.offsetWidth - child.clientWidth + "px";
                    child.style.right = -child.offsetWidth + child.clientWidth + "px";
                }
                hideScrollbar();
                var conn = new WebSocket('ws://<?php echo $_SERVER['SERVER_ADDR'];?>:8000');
                var messages = $('#messages');
                var empty_msg = $('<li class="message"></li>');
                var msg_obj = {
                    command: "subscribe",
                    channel: "mychannel"
                };
                function animateMessage(msg){
                    setTimeout(function(){
                        msg.addClass('message-visible');
                    },500);
                }
                conn.onopen = function(e){
                    var info = empty_msg.clone()
                                .text('You Joined the chat. Start sending messages...')
                                .addClass('info success');
                    messages.append(info);
                    animateMessage(info);
                    conn.send(JSON.stringify(msg_obj));
                };
                conn.onerror = function(){
                    var info = empty_msg.clone()
                            .text('Something went wrong. Try again.')
                            .addClass('info error');
                    messages.append(info);
                    animateMessage(info);
                };
                conn.onclose = function(){
                    var info = empty_msg.clone()
                            .text('Disconnected from chat.')
                            .addClass('info error');
                    messages.append(info);
                    animateMessage(info);
                };
                conn.onmessage = function(e){
                    var append = true;
                    var data = JSON.parse(e.data);
                    var info = empty_msg.clone().html(data.message);
                    if(data.type==='notification'){
                        info.addClass('info');
                        if(data.comes_under==='success'){
                            info.addClass('success');
                        } else if(data.comes_under==='error'){
                            info.addClass('error');
                        }
                    } else if(data.type==='subscribed'){
                        $('#user-id').text(data.message);
                        append = false;
                    }
                    if(append){
                        messages.append(info);
                        animateMessage(info);
                        hideScrollbar();
                        messages.scrollTop(messages[0].scrollHeight);
                    }
                };
                function sendMessage(msg){
                    msg_obj.command = 'message';
                    msg_obj.message = msg;
                    conn.send(JSON.stringify(msg_obj));
                    var info = empty_msg.clone().html(msg).addClass('sent-by-me');
                    messages.append(info);
                    animateMessage(info);
                    hideScrollbar();
                    messages.scrollTop(messages[0].scrollHeight);
                }
                $('#message').keyup(function(e){
                    e.preventDefault();
                    if(e.keyCode===13){
                        sendMessage($(this).val());
                        $(this).val('');
                    }
                });
                $('#send-btn').click(function(e){
                    e.preventDefault();
                    sendMessage($('#message').val());
                    $('#message').val('');
                });
            });
        </script>
    </body>
</html>
