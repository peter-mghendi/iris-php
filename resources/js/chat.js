window.pagemodel = function () {
    return {
        callPlaced: false,
        client: null,
        localStream: null,
        mutedAudio: false,
        mutedVideo: false,
        userOnlineChannel: null,
        onlineUsers: [],
        incomingCall: false,
        incomingCaller: "",
        agoraChannel: null,

        initPage: function () {
            this.initUserOnlineChannel();
            this.initUserOnlineListeners();
        },

        /**
         * Presence Broadcast Channel Listeners and Methods
         * Provided by Laravel.
         * Websockets with Pusher
         */
        initUserOnlineChannel: function () {
            this.userOnlineChannel = window.Echo.join("online-channel");
        },

        initUserOnlineListeners: function () {
            this.userOnlineChannel.here((users) => {
                this.onlineUsers = users;
            });
            this.userOnlineChannel.joining((user) => {
                // check user availability
                const joiningUserIndex = this.onlineUsers.findIndex(
                    (data) => data.id === user.id
                );
                if (joiningUserIndex < 0) {
                    this.onlineUsers.push(user);
                }
            });
            this.userOnlineChannel.leaving((user) => {
                const leavingUserIndex = this.onlineUsers.findIndex(
                    (data) => data.id === user.id
                );
                this.onlineUsers.splice(leavingUserIndex, 1);
            });
            // listen to incomming call
            this.userOnlineChannel.listen("MakeVideoCall", ({ data }) => {
                if (parseInt(data.userToCall) === parseInt(authuserid)) {
                    const callerIndex = this.onlineUsers.findIndex(
                        (user) => user.id === data.from
                    );
                    this.incomingCaller = this.onlineUsers[callerIndex]["name"];
                    this.incomingCall = true;
                    // the channel that was sent over to the user being called is what
                    // the receiver will use to join the call when accepting the call.
                    this.agoraChannel = data.channelName;
                }
            });
        },

        getUserOnlineStatus: function (id) {
            const onlineUserIndex = this.onlineUsers.findIndex(
                (data) => data.id === id
            );
            if (onlineUserIndex < 0) {
                return "Offline";
            }
            return "Online";
        },

        placeCall: async function (id, calleeName) {
            try {
                // channelName = the caller's and the callee's id. you can use anything. tho.
                const channelName = `${authuser}_${calleeName}`;
                const tokenRes = await this.generateToken(channelName);
                // Broadcasts a call event to the callee and also gets back the token
                await axios.post("/call", {
                    user_to_call: id,
                    username: authuser,
                    channel_name: channelName,
                });
                this.initializeAgora();
                this.joinRoom(tokenRes.data, channelName);
            } catch (error) {
                console.log(error);
            }
        },

        acceptCall: async function () {
            this.initializeAgora();
            const tokenRes = await this.generateToken(this.agoraChannel);
            this.joinRoom(tokenRes.data, this.agoraChannel);
            this.incomingCall = false;
            this.callPlaced = true;
        },

        declineCall: function () {
            // You can send a request to the caller to
            // alert them of rejected call
            this.incomingCall = false;
        },

        generateToken: function (channelName) {
            return axios.post("/token", {
                channelName,
            });
        },

        /**
         * Agora Events and Listeners
         */
        initializeAgora: function () {
            this.client = AgoraRTC.createClient({ mode: "rtc", codec: "h264" });
            this.client.init(
                this.agora_id,
                () => {
                    console.log("AgoraRTC client initialized");
                },
                (err) => {
                    console.log("AgoraRTC client init failed", err);
                }
            );
        },

        joinRoom: function (token, channel) {
            this.client.join(
                token,
                channel,
                authuser,
                (uid) => {
                    console.log("User " + uid + " join channel successfully");
                    this.callPlaced = true;
                    this.createLocalStream();
                    this.initializedAgoraListeners();
                },
                (err) => {
                    console.log("Join channel failed", err);
                }
            );
        },

        initializedAgoraListeners: function () {
            //   Register event listeners
            this.client.on("stream-published", function (evt) {
                console.log("Publish local stream successfully");
                console.log(evt);
            });
            //subscribe remote stream
            this.client.on("stream-added", ({ stream }) => {
                console.log("New stream added: " + stream.getId());
                this.client.subscribe(stream, function (err) {
                    console.log("Subscribe stream failed", err);
                });
            });
            this.client.on("stream-subscribed", (evt) => {
                // Attach remote stream to the remote-video div
                evt.stream.play("remote-video");
                this.client.publish(evt.stream);
            });
            this.client.on("stream-removed", ({ stream }) => {
                console.log(String(stream.getId()));
                stream.close();
            });
            this.client.on("peer-online", (evt) => {
                console.log("peer-online", evt.uid);
            });
            this.client.on("peer-leave", (evt) => {
                var uid = evt.uid;
                var reason = evt.reason;
                console.log("remote user left ", uid, "reason: ", reason);
            });
            this.client.on("stream-unpublished", (evt) => {
                console.log(evt);
            });
        },

        createLocalStream: function () {
            this.localStream = AgoraRTC.createStream({
                audio: true,
                video: true,
            });
            // Initialize the local stream
            this.localStream.init(
                () => {
                    // Play the local stream
                    this.localStream.play("local-video");
                    // Publish the local stream
                    this.client.publish(this.localStream, (err) => {
                        console.log("publish local stream", err);
                    });
                },
                (err) => {
                    console.log(err);
                }
            );
        },

        endCall: function () {
            this.localStream.close();
            this.client.leave(
                () => {
                    console.log("Leave channel successfully");
                    this.callPlaced = false;
                },
                (err) => {
                    console.log("Leave channel failed");
                }
            );
        },

        handleAudioToggle: function () {
            if (this.mutedAudio) {
                this.localStream.unmuteAudio();
                this.mutedAudio = false;
            } else {
                this.localStream.muteAudio();
                this.mutedAudio = true;
            }
        },

        handleVideoToggle: function () {
            if (this.mutedVideo) {
                this.localStream.unmuteVideo();
                this.mutedVideo = false;
            } else {
                this.localStream.muteVideo();
                this.mutedVideo = true;
            }
        },
    };
};
