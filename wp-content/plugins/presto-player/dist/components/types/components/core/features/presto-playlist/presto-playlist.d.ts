import { PlaylistItem } from '../../../../interfaces';
export declare class PrestoPlaylist {
    el: HTMLElement;
    /** Overlay component */
    private overlay;
    /** Array of videos from the Playlist */
    items: Array<PlaylistItem>;
    /** Title for the Playlist */
    heading: string;
    /** Count prefix string for the Playlist - Singular */
    listTextSingular: string;
    /** Count prefix string for the Playlist - Plural */
    listTextPlural: string;
    /** Transition duration for next video. */
    transitionDuration: number;
    /** Stores current video that is shown in the preview. */
    currentPlaylistItem: PlaylistItem;
    /** Stores current video Plyr object that is shown in the preview. */
    currentPlyr: any;
    /** Is a video playing. */
    playing: boolean;
    /**
     * Listening to the Rewatch Video button click event.
     */
    rewatch(): void;
    /**
     * Listening to the Next Video button click event.
     */
    next(): void;
    /**
     * Plays the video, adds overlay and Presto Video styles as soon as a new object is assigned.
     * @param value Current value
     * @param previousValue Previous value
     */
    handleCurrentPlay(value: any, previousValue: any): void;
    /**
     * Adds overlay to the player which will regulate the Next video button.
     */
    addOverlay(): void;
    /**
     * Lifecycle: Component will load.
     */
    componentWillLoad(): void;
    /**
     * Handles the click on the playlist item.
     * @param item PlaylistItem
     */
    handleItemClick(item: PlaylistItem): void;
    /**
     * Assign the next item in the playlist as a current item.
     */
    handleNext(): void;
    /**
     * Play the current video.
     */
    handlePlay(): void;
    /**
     * Pause the current video.
     */
    handlePause(): void;
    /**
     * Get the next item in the playlist.
     * @returns PlaylistItem Next item in the playlist.
     */
    getNextItem(): PlaylistItem;
    /**
     * Checks if the current item is the last item in the playlist.
     * @returns boolean True if the current item is the last item in the playlist.
     */
    isLastItem(): boolean;
    /**
     * Get the title of the next item in the playlist.
     * @returns string Title of the next item in the playlist.
     */
    getNextItemTitle(): string;
    /**
     * Rendering the component.
     * @returns Web Component
     */
    render(): any;
}
