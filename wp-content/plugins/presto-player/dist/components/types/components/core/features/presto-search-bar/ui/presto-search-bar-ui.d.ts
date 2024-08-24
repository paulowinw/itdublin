import { EventEmitter } from '../../../../../stencil-public-runtime';
export declare class PrestoSearchBarUi {
    private placeholderElement;
    private input;
    /** The value for the search. */
    value: string;
    /** The placeholder. */
    placeholder: string;
    /** Has results */
    hasNavigation: boolean;
    /** Is this focused */
    focused: boolean;
    /** The placeholder width. */
    placeholderWidth: number;
    /** Previous is navigated.*/
    previousNav: EventEmitter<void>;
    /** Next is navigated */
    nextNav: EventEmitter<void>;
    /** Search is performed */
    search: EventEmitter<string>;
    /** Search is performed */
    focusChange: EventEmitter<boolean>;
    /** Handle the search */
    handleSearch(e: any): void;
    /** Handle the focus of the input. */
    handleFocus(): void;
    handleBlur(): void;
    componentDidLoad(): void;
    handlePlaceholderSize(): void;
    handleValueChange(): void;
    watchPropHandler(focus: boolean): void;
    handleClick(): void;
    handleClear(e: any): boolean;
    handleNext(e: any): void;
    handlePrevious(e: any): void;
    /**
     * Rendering the component
     */
    render(): any;
}
