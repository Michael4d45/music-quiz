<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Music Track Player
        </x-slot>

        <x-slot name="description">
            Preview and listen to the music track
        </x-slot>

        <div class="space-y-6">
            <div x-data="musicTrackPlayerWidget"
                 x-init="initPlayer()">
                <!-- Track Information -->
                <div class="bg-secondary-bg rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-secondary"
                                x-text="track?.title || 'No track loaded'"></h3>
                            <p class="text-gray-600 dark:text-gray-400"
                               x-text="track?.artist_name || ''"></p>
                            <p class="text-sm text-muted"
                               x-show="track?.album_name"
                               x-text="track?.album_name"></p>
                        </div>
                        <div class="text-right text-sm text-muted">
                            <div x-show="track?.release_year" x-text="track?.release_year"></div>
                            <div x-show="track?.genre" x-text="track?.genre"></div>
                        </div>
                    </div>

                    <!-- Quiz Question Context -->
                    <div x-show="question" class="mt-3 p-3 bg-info-50 dark:bg-info-900/20 rounded border border-info-200 dark:border-info-800">
                        <div class="flex items-center gap-2 text-sm text-(--info)">
                            <x-heroicon-o-question-mark-circle class="w-5 h-5" />
                            <span class="font-medium">Quiz Question:</span>
                            <span x-text="question?.question_type?.label || 'Unknown'"></span>
                        </div>
                        <div x-show="question?.prompt_text" class="mt-1 text-(--info)" x-text="question?.prompt_text"></div>
                        <div x-show="question?.media_start_seconds || question?.media_end_seconds" class="mt-2 text-xs text-(--info)">
                            <span x-show="question?.media_start_seconds">
                                Start: <span x-text="formatTime(question?.media_start_seconds)"></span>
                            </span>
                            <span x-show="question?.media_start_seconds && question?.media_end_seconds"> | </span>
                            <span x-show="question?.media_end_seconds">
                                End: <span x-text="formatTime(question?.media_end_seconds)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Audio Player -->
                <div class="space-y-4">
                    <!-- HTML Audio Element -->
                    <audio
                        x-ref="audioPlayer"
                        x-show="currentSource && playerType === 'html-audio'"
                        x-on:loadedmetadata="onAudioLoaded"
                        x-on:timeupdate="onTimeUpdate"
                        x-on:ended="onAudioEnded"
                        x-on:error="onAudioError"
                        class="hidden">
                    </audio>

                    <!-- YouTube Player -->
                    <div
                        x-ref="youtubePlayer"
                        x-show="currentSource && playerType === 'youtube'"
                        class="w-full aspect-video bg-black rounded-lg overflow-hidden">
                    </div>

                    <!-- Source Selection -->
                    <div x-show="availableSources.length > 0">
                        <label class="block text-sm font-medium text-secondary mb-2">
                            Available Sources
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="source in availableSources" :key="source.id">
                                <button
                                    x-on:click="selectSource(source)"
                                    :class="[
                                        'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                        currentSource?.id === source.id
                                            ? 'bg-(--primary) text-white'
                                            : 'bg-card text-secondary border border-secondary hover-bg-secondary'
                                    ]">
                                    <img x-show="source.icon_url" :src="source.icon_url" class="w-4 h-4" alt="">
                                    <x-heroicon-o-musical-note x-show="!source.icon_url" class="w-4 h-4" />
                                    <span x-text="source.display_name"></span>
                                    <span x-show="source.preview_url || source.full_url" class="text-xs opacity-75">
                                        (<span x-text="source.preview_url ? 'Preview' : 'Full'"></span>)
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Playback Controls -->
                    <div x-show="currentSource" class="bg-secondary-bg rounded-lg p-4">
                        <div class="flex items-center gap-4 mb-4">
                            <!-- Play/Pause Button -->
                            <button
                                x-on:click="togglePlayPause"
                                :disabled="isLoading"
                                class="flex items-center justify-center w-12 h-12 bg-(--primary) hover:bg-(--primary-hover) disabled:bg-(--secondary-border) text-white rounded-full transition-colors">
                                <x-heroicon-o-play x-show="!isPlaying" class="w-6 h-6 ml-1" />
                                <x-heroicon-o-pause x-show="isPlaying" class="w-6 h-6" />
                            </button>

                            <!-- Progress Bar -->
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="h-2 bg-(--secondary-border) rounded-full cursor-pointer"
                                         x-on:click="seekToPosition($event)">
                                        <div
                                            class="h-full bg-(--primary) rounded-full transition-all"
                                            :style="`width: ${progress}%`">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs text-muted mt-1">
                                    <span x-text="formatTime(currentTime)"></span>
                                    <span x-text="formatTime(duration)"></span>
                                </div>
                            </div>

                            <!-- Volume Control -->
                            <div class="flex items-center gap-2">
                                <button x-on:click="toggleMute" class="text-secondary hover:text-(--secondary-hover)">
                                    <x-heroicon-o-speaker-wave x-show="!isMuted" class="w-5 h-5" />
                                    <x-heroicon-o-speaker-x-mark x-show="isMuted" class="w-5 h-5" />
                                </button>
                                <input
                                    type="range"
                                    min="0"
                                    max="1"
                                    step="0.1"
                                    x-model="volume"
                                    x-on:input="setVolume"
                                    class="w-20">
                            </div>
                        </div>

                        <!-- Loading/Error States -->
                        <div x-show="isLoading" class="text-center text-gray-500 dark:text-gray-400">
                            Loading audio...
                        </div>

                        <div x-show="error" class="text-center text-(--danger)">
                            <span x-text="error"></span>
                        </div>
                    </div>

                    <!-- No Sources Available -->
                    <div x-show="availableSources.length === 0 && track" class="text-center py-8 text-muted">
                        <x-heroicon-o-musical-note class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>No audio sources available for this track.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@script
    <script>
        Alpine.data('musicTrackPlayerWidget', () => ({
            track: @json($this->track ?? null),
            question: @json($this->question ?? null),
            audioPlayer: null,
            youtubePlayer: null,
            youtubePlayerInstance: null,
            youtubeAPIReady: false,
            currentSource: null,
            availableSources: [],
            playerType: 'html-audio',
            isPlaying: false,
            isLoading: false,
            isMuted: false,
            currentTime: 0,
            duration: 0,
            volume: 0.8,
            progress: 0,
            error: null,

            initPlayer() {
                this.audioPlayer = this.$refs.audioPlayer;
                this.youtubePlayer = this.$refs.youtubePlayer;
                this.buildAvailableSources();
                if (this.availableSources.length > 0) {
                    this.selectSource(this.availableSources[0]);
                }
            },

            buildAvailableSources() {
                this.availableSources = [];
                if (this.track?.primary_source) {
                    const primary = {
                        id: 'primary',
                        display_name: this.track.primary_source.display_name,
                        icon_url: this.track.primary_source.icon_url,
                        preview_url: null,
                        full_url: null,
                        embed_url: null,
                        is_primary: true
                    };
                    const primaryLink = this.track.source_links?.find(link =>
                        link.source_id === this.track.primary_source_id
                    );
                    if (primaryLink) {
                        primary.preview_url = primaryLink.preview_url;
                        primary.full_url = primaryLink.full_url;
                        primary.embed_url = primaryLink.embed_url;
                    }
                    if (primary.preview_url || primary.full_url || primary.embed_url) {
                        this.availableSources.push(primary);
                    }
                }
                this.track?.source_links?.forEach(link => {
                    if (link.source_id !== this.track.primary_source_id) {
                        if (link.preview_url || link.full_url || link.embed_url) {
                            this.availableSources.push({
                                id: link.id,
                                display_name: link.source.display_name,
                                icon_url: link.source.icon_url,
                                preview_url: link.preview_url,
                                full_url: link.full_url,
                                embed_url: link.embed_url,
                                is_primary: false
                            });
                        }
                    }
                });
            },

            selectSource(source) {
                this.currentSource = source;
                this.error = null;
                this.isLoading = true;
                this.playerType = source.display_name.toLowerCase().includes('youtube') ? 'youtube' : 'html-audio';
                if (this.playerType === 'html-audio' && this.audioPlayer) {
                    this.audioPlayer.pause();
                    this.audioPlayer.src = source.preview_url || source.full_url;
                    this.audioPlayer.load();
                }
            },

            togglePlayPause() {
                if (this.isLoading) return;
                if (this.playerType === 'html-audio' && this.audioPlayer) {
                    if (this.isPlaying) {
                        this.audioPlayer.pause();
                    } else {
                        this.audioPlayer.play();
                    }
                }
            },

            seekToPosition(event) {
                if (!this.duration) return;
                const rect = event.target.getBoundingClientRect();
                const percent = (event.clientX - rect.left) / rect.width;
                if (this.audioPlayer) {
                    this.audioPlayer.currentTime = percent * this.duration;
                }
            },

            toggleMute() {
                this.isMuted = !this.isMuted;
                if (this.audioPlayer) {
                    this.audioPlayer.muted = this.isMuted;
                }
            },

            setVolume() {
                if (this.audioPlayer) {
                    this.audioPlayer.volume = this.volume;
                }
            },

            onAudioLoaded() {
                this.duration = this.audioPlayer.duration;
                this.isLoading = false;
                this.error = null;
            },

            onTimeUpdate() {
                this.currentTime = this.audioPlayer.currentTime;
                this.progress = (this.currentTime / this.duration) * 100;
                this.isPlaying = !this.audioPlayer.paused;
            },

            onAudioEnded() {
                this.isPlaying = false;
            },

            onAudioError() {
                this.isLoading = false;
                this.error = 'Failed to load audio.';
                this.isPlaying = false;
            },

            formatTime(seconds) {
                if (!seconds) return '0:00';
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }
        }));
    </script>
@endscript
