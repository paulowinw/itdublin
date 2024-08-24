import { EventEmitter } from '../../../stencil-public-runtime';
export declare class PrestoPlayerButton {
    button: HTMLElement | HTMLAnchorElement;
    private hasFocus;
    private hasLabel;
    private hasPrefix;
    private hasSuffix;
    /** The button's type. */
    type: 'default' | 'primary' | 'success' | 'info' | 'warning' | 'danger' | 'text';
    /** The button's size. */
    size: 'small' | 'medium' | 'large';
    /** Draws the button with a caret for use with dropdowns, popovers, etc. */
    full?: boolean;
    /** Disables the button. */
    disabled?: boolean;
    /** Indicates if activating the button should submit the form. Ignored when `href` is set. */
    submit?: boolean;
    /** An optional name for the button. Ignored when `href` is set. */
    name: string;
    /** An optional value for the button. Ignored when `href` is set. */
    value: string;
    /** When set, the underlying button will be rendered as an `<a>` with this `href` instead of a `<button>`. */
    href: string;
    /** Tells the browser where to open the link. Only used when `href` is set. */
    target: '_blank' | '_parent' | '_self' | '_top';
    /** Tells the browser to download the linked file as this filename. Only used when `href` is set. */
    download: string;
    /** Emitted when the button loses focus. */
    prestoBlur: EventEmitter<void>;
    /** Emitted when the button gains focus. */
    prestoFocus: EventEmitter<void>;
    componentWillLoad(): void;
    /** Simulates a click on the button. */
    click(): void;
    /** Sets focus on the button. */
    focus(options?: FocusOptions): void;
    /** Removes focus from the button. */
    blur(): void;
    handleSlotChange(): void;
    handleBlur(): void;
    handleFocus(): void;
    handleClick(event: MouseEvent): void;
    render(): any;
}
