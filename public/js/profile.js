buttons = $(".profile-btn");
menus = $(".menu-info");

menus.each(function () {
    $(this).hide();
})

$(menus[0]).show();
buttons[0].classList.add("active");

buttons.click(function (){
    if(this.classList.contains("active")) return;
    let btn = this;
    buttons.each(function (index) {
        if(this.classList.contains("active")) this.classList.remove("active");
        if(this == btn) showMenu(index);
    })

    this.classList.add("active");
})

function showMenu(id) {
    menus.each(function () {
        $(this).hide();
    })

    $(menus[id]).show();
    /*let guild_current_msg = document.getElementById("guild-current-msg");
    guild_current_msg.scrollTop = guild_current_msg.scrollHeight;*/
}

// MY PROFILE SECTION
let my_profile_btn = $('#menu-info-btn');
let my_profile_username = $('#menu-info-username');
my_profile_btn.click(function () {
    toggleEditor();
})

let editor = false;
function toggleEditor() {
    if(!editor) {
        let username = my_profile_username.text();
        my_profile_username.html('<input type="text" placeholder="Nouveau Pseudo" value="' + username + '"/>')
        my_profile_btn.text('Confirmer');
    }else {
        let username = my_profile_username.children().val();
        changeUsername(username);
        my_profile_username.html(username)
        my_profile_btn.text('Modifier');
    }
    editor = !editor;
}

function changeUsername(input) {
    $.ajax({
        url:"../ajax/changeUsername",
        dataType:"json",
        method:"POST",
        data: {"input":input},
    })
}

// POPUPS SECTION
let overlay = $("#overlay");
let popups = $(".menu-popup");
let btn = $(".popup-btn");
let close_btn = $(".close");
overlay.hide();
popups.hide();

btn.click(function (){

    let type = this.dataset.type;
    let parameter = this.dataset.parameter;

    switch (type){
        default:
            openPopup(type, parameter);
            break;
    }
})

let friend_msg_send = $("#friends-new-msg-send");
function openPopup(type, parameter){
    overlay.show();
    let popup = $("#"+type+"-popup");
    popup.show();
    switch (type) {
        case "addfriend":
            getNonRelationnedUsers();
            break;
        case "friends":
            friend_msg_send[0].dataset.friend_id = parameter;
            getFriendMessages(parameter, popup, true);
            break;
    }
}

function closePopup(){
    overlay.hide();
    popups.hide();
}

close_btn.click(function (){
    closePopup();
})

overlay.click(function (){
    closePopup();
})

// FRIENDS

// UPDATE AND TOGGLE ERRORS
function toggleInfos(type, values) {
    let info_block_html = $('.info-block');
    let infos_html = $('.infos');
    let msg_title = info_block_html[0].childNodes[1];
    switch (type) {
        case 'error':
            info_block_html.css('background-color', 'var(--error)');
            msg_title.innerHTML = '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Une ou plusieurs erreurs sont parvenues';
            break;
        case 'success':
            info_block_html.css('background-color', 'var(--success)');
            msg_title.innerHTML = '<i class="fas fa-check-circle" aria-hidden="true"></i> Une ou plusieurs actions ont été réalisées avec succès';
            break;
        default:
            info_block_html.css('background-color', 'var(--grey)');
            msg_title.innerHTML = '';
            break;
    }
    infos_html.each(function () {
        this.innerHTML = '';
    });

    if(values !== null) {
        info_block_html.show();
        $.each(values, function (index,value) {
            infos_html.each(function () {
                this.innerHTML += "- " + value + "<br>";
            });
        })
    }else info_block_html.hide();
}

toggleInfos('', null);

function addFriend(id) {
    $.ajax({
        url:"../ajax/addFriend",
        dataType:"json",
        method:"POST",
        data: {"friend_id":id},
        success: function (response) {

            if(!response.success) {
                toggleInfos('error', [response.msg]);
                setTimeout(function () {
                    toggleInfos('error', null);
                }, 4000)
            }

            getNonRelationnedUsers();

            toggleInfos('success', [response.msg]);
            setTimeout(function () {
                toggleInfos('success', null);
            }, 4000)
        },
    })
}

function removeFriend(id) {
    $.ajax({
        url:"../ajax/removeFriend",
        dataType:"json",
        method:"POST",
        data: {"friend_id":id},
        success: function (response) {
            if(!response.success) {
                toggleInfos('error', [response.msg]);
                setTimeout(function () {
                    toggleInfos('error', null);
                }, 4000)
            }

            getNonRelationnedUsers();

            toggleInfos('success', [response.msg]);
            setTimeout(function () {
                toggleInfos('success', null);
            }, 4000)
        },
    })
}

let requests_button = $('#waiting-friend')[0];
requests_button.setAttribute('data-after', '(0)')

// GET FRIEND REQUEST SEND TO USER
function getFriendRequests() {
    $.ajax({
        url:"../ajax/getFriendRequests",
        dataType:"json",
        method:"POST",
        success: function (response) {
            let friends_requests = $('#request-friend-list');
            friends_requests[0].innerHTML = '';

            if(!response.exist_request) {
                let li = document.createElement('li');
                li.innerHTML = 'Aucune demande reçue';
                li.classList.add('no-request');
                friends_requests.push(li);
                requests_button.setAttribute('data-after', '(0)')
                return;
            }

            requests_button.setAttribute('data-after', '(' + response.requests.length + ')')
            response.requests.forEach(user => {
                let li = document.createElement('li');

                    let img = document.createElement('img');
                    img.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email='+user.email+'&size=HR360x360';
                    li.appendChild(img);

                    let h3 = document.createElement('h3');
                    h3.innerHTML = user.username;
                    li.appendChild(h3);

                    let button_accept = document.createElement('button');
                    button_accept.classList.add('add-friend');
                    button_accept.dataset.friend = user.id;
                    button_accept.innerHTML = 'Accepter+';
                    li.appendChild(button_accept);

                    let button_remove = document.createElement('button');
                    button_remove.classList.add('remove-friend');
                    button_remove.dataset.friend = user.id;
                    button_remove.innerHTML = 'Refuser-';
                    li.appendChild(button_remove);

                friends_requests.append(li);
            });
        },
    })
}

getFriendRequests();

//GET USERS NOT FRIEND WITH USER
function getNonRelationnedUsers(value = '') {
    $.ajax({
        url:"../ajax/getNonRelationnedUsers",
        dataType:"json",
        method:"POST",
        data:{"search":value},
        success: function (response) {
            let users_list = $('#add-friend-list');
            users_list[0].innerHTML = '';

            if(!response.exist_users) {
                let li = document.createElement('li');
                li.innerHTML = 'Aucune demande reçue';
                li.classList.add('no-request');
                users_list.push(li);
                return;
            }

            response.users.forEach(user => {
                let li = document.createElement('li');

                let img = document.createElement('img');
                img.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email='+user.email+'&size=HR360x360';
                img.alt = '#';
                li.appendChild(img);

                let h3 = document.createElement('h3');
                h3.innerHTML = user.username;
                li.appendChild(h3);

                let button_accept = document.createElement('button');
                button_accept.classList.add('add-friend');
                button_accept.dataset.friend = user.id;
                button_accept.innerHTML = 'Ajouter+';
                li.appendChild(button_accept);

                users_list.append(li);
            });
        },
    })
}

getNonRelationnedUsers();

//GET USERS NOT FRIEND WITH USER
function getCurrentFriends(search = '') {
    $.ajax({
        url:"../ajax/getCurrentFriends",
        dataType:"json",
        method:"POST",
        data:{'search':search},
        success: function (response) {

            let friends_list_img = $('#friends-img-list');
            if(!response.search) friends_list_img.html('');

            let friends_list = $('#friend-list-table');
            friends_list.html('');

            if(!response.exist_friends) {

                let div = document.createElement("div");
                div.classList.add("friend-img");

                let img = document.createElement("img");
                img.src = "../img/profile/unknown.png";
                img.alt = "#";
                div.appendChild(img);

                let p = document.createElement("p");
                p.innerText = 'Aucun ami';
                div.appendChild(p);

                friends_list_img.append(div);

                return;
            }

            response.users.forEach(user => {

                // FRIENDS POPUP LIST
                let tr = document.createElement("tr");

                let td_pp = document.createElement("td");
                td_pp.classList.add("friend-list-table-img");
                let pp = document.createElement("img");
                pp.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email='+user.email+'&size=HR360x360';
                pp.alt = '#';
                td_pp.appendChild(pp);
                tr.appendChild(td_pp);

                let td_username = document.createElement("td");
                td_username.id = "menu-info-username";
                td_username.innerText = user.username;
                tr.appendChild(td_username);

                let td_discuss = document.createElement("td");
                td_discuss.innerText = "Converser";
                td_discuss.classList.add("friend-list-table-button");
                td_discuss.dataset.type = "discuss";
                td_discuss.dataset.parameter = user.id;
                tr.appendChild(td_discuss);

                let td_remove = document.createElement("td");
                td_remove.innerText = "Supprimer";
                td_remove.classList.add("friend-list-table-button");
                td_remove.dataset.type = "remove";
                td_remove.dataset.parameter = user.id;
                tr.appendChild(td_remove);

                friends_list.append(tr);

                // FRIENDS IMG LIST

                if(!response.search) {

                    let div = document.createElement("div");
                    div.classList.add("friend-img");

                    let img = document.createElement("img");
                    img.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email=' + user.email + '&size=HR360x360';
                    img.alt = "#";
                    div.appendChild(img);

                    let i = document.createElement("i");
                    i.classList.add("offline");
                    i.classList.add("fas");
                    i.classList.add("fa-circle");
                    div.appendChild(i);

                    let p = document.createElement("p");
                    p.innerText = user.username;
                    div.appendChild(p);

                    friends_list_img.append(div);
                }

            });
        },
    })
}

getCurrentFriends();

// FRIENDS MESSAGES
let request_message_interval = null;
function getFriendMessages(friend_id, popup, first = false) {
    $.ajax({
        url:"../ajax/getFriendMessages",
        dataType:"json",
        method:"POST",
        data: {"friend_id":friend_id},
        success: function (response) {
            popup.children()[1].innerText = "Conversation avec " + response.username + ":";
            let msg_block = $("#friends-current-msg");
            let is_end_scroll = msg_block[0].scrollHeight - msg_block[0].scrollTop === msg_block[0].clientHeight;
            let current_scroll = msg_block[0].scrollTop;
            msg_block.html("");
            let messages = response.messages;

            messages.forEach(message => {

                let block = document.createElement("div");
                block.id = "friends-current-msg-block";

                let img = document.createElement('img');
                img.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email='+message.owner.email+'&size=HR360x360';
                img.alt = '#';
                block.appendChild(img);

                let div = document.createElement("div");
                let h5 = document.createElement("h5");
                h5.innerText = message.owner.username;
                div.appendChild(h5);

                let p = document.createElement("p");
                p.innerText = message.content;
                div.appendChild(p);

                block.appendChild(div);

                if(message.owner_id === $("#myprofile")[0].dataset.id) {
                    block.classList.add("owner");
                    block.appendChild(div);
                    block.appendChild(img);
                }else {
                    block.appendChild(img);
                    block.appendChild(div);
                }

                msg_block.append(block);
            })

            if(first || is_end_scroll) {
                msg_block[0].scrollTop = msg_block[0].scrollHeight
            }else {
                msg_block[0].scrollTop = current_scroll;
            }

            if(request_message_interval === null) {
                request_message_interval = setInterval(function () {
                    if(!popup.is(":visible")) {
                        clearInterval(request_message_interval);
                        request_message_interval = null;
                        return;
                    }
                    getFriendMessages(friend_id, popup)
                }, 3000)
            }

        },
    })
}

function sendFriendMessage(friend_id, value) {
    let popup = $("#friends-popup");
    $.ajax({
        url:"../ajax/sendFriendMessage",
        dataType:"json",
        method:"POST",
        data: {"friend_id":friend_id, "value":value},
        success: function (response) {
            if(response.success) {
                getFriendMessages(friend_id, popup);
            }
        },
    })
}

friend_msg_send.click(function () {
    let textarea = $("#friends-new-msg-left");
    let value = textarea.val();
    sendFriendMessage(this.dataset.friend_id, value);
    textarea.val("");
})

// FRIENDS CONVERSATIONS
function getLastConversations() {
    $.ajax({
        url:"../ajax/getLastConversations",
        dataType:"json",
        method:"POST",
        success: function (response) {

            let friends_msg_block = $("#friends-msg-block");
            friends_msg_block.html('');

            if(!response.success) {
                return;
            }

            response.values.forEach(value => {

                let div = document.createElement("div");
                div.classList.add("msg-block");

                let img = document.createElement("img");
                img.src = 'https://outlook.office.com/owa/service.svc/s/GetPersonaPhoto?email='+value.user.email+'&size=HR360x360';
                img.alt = '#';
                div.appendChild(img);

                let p = document.createElement("p");
                p.classList.add("msg-block-msg");
                p.innerText = value.last_msg.content;
                div.appendChild(p);

                let button = document.createElement("button");
                button.innerText = "Converser";
                button.classList.add("friend-list-table-button");
                button.dataset.type = "discuss";
                button.dataset.parameter = value.user.id;
                div.appendChild(button);

                friends_msg_block.append(div);

            })

        },
    })
}

getBddInformations();

setInterval(getBddInformations, 8000);

function getBddInformations() {
    getFriendRequests();
    //getNonRelationnedUsers();
    getCurrentFriends();
    getLastConversations();
}

// ADD FRIEND OR ACCEPT REQUEST
$(document).on('click', '.add-friend', function() {
    let friend_id = this.dataset.friend;
    addFriend(friend_id);
});

// REMOVE FRIEND OR DECLINE REQUEST
$(document).on('click', '.remove-friend', function() {
    let friend_id = this.dataset.friend;
    removeFriend(friend_id);
});

// FRIENDS LIST
$(document).on('click', '.friend-list-table-button', function() {
    let type = this.dataset.type;
    let id = this.dataset.parameter;

    closePopup();

    switch (type) {
        case "remove":
            removeFriend(id);
            break;
        case "discuss":
            openPopup("friends", id)
            break;
    }
})

$('#friend-list-search').on('input', function () {
   getCurrentFriends(this.value)
})

$('#add-friend-search').on('input', function () {
    getNonRelationnedUsers(this.value)
})