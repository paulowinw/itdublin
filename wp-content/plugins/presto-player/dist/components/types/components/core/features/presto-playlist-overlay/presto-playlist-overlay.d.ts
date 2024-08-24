import { EventEmitter } from '../../../../stencil-public-runtime';
export declare class PrestoPlaylistOverlay {
    /**	Caching timout action */
    private timeout;
    /** Title of the upcoming next playlist item */
    nextItemTitle: string;
    /** String for the Button that says `Next Video` */
    nextItemString: string;
    /** Transition duration for next video. */
    transitionDuration: number;
    /** Flag to handle if this item is the last item in the list. */
    isLastItem: boolean;
    /** Rewatch item event */
    rewatch: EventEmitter<void>;
    /** Next item play event */
    next: EventEmitter<void>;
    /** Class value for the Next button to show the fill animation. */
    animating: boolean;
    /** Visibility flag for the Overlay */
    show: boolean;
    /**
     * Watch for show changes
     */
    handleShow(): void;
    /**
     * Handle the rewatch click event.
     */
    handleRewatch(): void;
    /**
     * Handle the next click event.
     */
    handleNext(): void;
    /**
     * Lifecycle: disconnected
     */
    disconnectedCallback(): void;
    handleAnimatingChange(val: any): void;
    /**
     * Rendering the component
     */
    render(): any;
}
