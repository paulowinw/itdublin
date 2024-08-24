export declare class PrestoSearchBar {
    /**
     * Props
     */
    value: string;
    player: any;
    /**
     * State
     */
    markers: any;
    currentMarkerLocation: number;
    /**
     * Handle search event
     * @param value Event
     */
    onSearch(e: any): void;
    /**
     * Handle previous arrow key click after search
     */
    onPreviousNav(): void;
    /**
     * Handle the event where search is hovered or focused
     */
    onFocusChange(focus: any): void;
    /**
     * Handle next arrow key click after search
     */
    onNextNav(): void;
    render(): any;
}
