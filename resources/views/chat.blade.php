<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="pagemodel()" x-init="initPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <div class="container p-4">
                        <div class="grid grid-cols-1">
                            <div class="col-span-1 text-center">
                                <img src="img/agora-logo.png" alt="Agora Logo" class="img-fuild" />
                            </div>
                        </div>
                    </div>

                    <div class="container p-4 my-5">
                        <div class="grid grid-cols-12">
                            <div class="col-auto">
                                {{-- TODO --}}
                                <div class="btn-group" role="group">
                                    <template x-for="user in allusers" :key="user.id">
                                        <button type="button" class="btn btn-primary mr-2"
                                            @click="placeCall(user.id, user.name)" x-text="`Call ${user.name}`">
                                            <span class="badge badge-light"
                                                x-text="getUserOnlineStatus(user.id)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Incoming Call  -->
                        <div class="grid grid-cols-1 my-5" x-show="incomingCall">
                            <div>
                                <p>
                                    Incoming call from <strong x-text="incomingCaller"></strong>
                                </p>
                                {{-- TODO --}}
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"
                                        @click="declineCall">
                                        Decline
                                    </button>
                                    <button type="button" class="btn btn-success ml-5" @click="acceptCall">
                                        Accept
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- End of Incoming Call  -->
                    </div>

                    <section id="video-container" x-show="callPlaced">
                        <div id="local-video"></div>
                        <div id="remote-video"></div>

                        {{-- TODO --}}
                        <div class="action-btns">
                            <button type="button" class="btn btn-info" @click="handleAudioToggle"
                                x-text="mutedAudio ? 'Unmute' : 'Mute'">
                            </button>
                            <button type="button" class="btn btn-primary mx-4" @click="handleVideoToggle"
                                x-text="mutedVideo ? 'ShowVideo' : 'HideVideo'">
                            </button>
                            <button type="button" class="btn btn-danger" @click="endCall">
                                EndCall
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const allusers = @json($users);
        const authuserid = @json(Auth::id());
        const authuser = @json(Auth::user()->name);
        const agora_id = @json(config('agora.app_id'));
    </script>
    <script src="https://cdn.agora.io/sdk/release/AgoraRTCSDK-3.5.2.js"></script>
    <script src="{{ mix('js/chat.js') }}" defer></script>
    @endpush

    @push('styles')
    <style>
        /* main {
          margin-top: 50px;
        } */

        #video-container {
            width: 700px;
            height: 500px;
            max-width: 90vw;
            max-height: 50vh;
            margin: 0 auto;
            border: 1px solid #099dfd;
            position: relative;
            box-shadow: 1px 1px 11px #9e9e9e;
            background-color: #fff;
        }

        #local-video {
            width: 30%;
            height: 30%;
            position: absolute;
            left: 10px;
            bottom: 10px;
            border: 1px solid #fff;
            border-radius: 6px;
            z-index: 2;
            cursor: pointer;
        }

        #remote-video {
            width: 100%;
            height: 100%;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            top: 0;
            z-index: 1;
            margin: 0;
            padding: 0;
            cursor: pointer;
        }

        .action-btns {
            position: absolute;
            bottom: 20px;
            left: 50%;
            margin-left: -50px;
            z-index: 3;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        /* #login-form {
          margin-top: 100px;
        } */
    </style>
    @endpush
</x-app-layout>