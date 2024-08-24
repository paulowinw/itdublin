export declare class PrestoDynamicOverlayUi {
    el: HTMLElement;
    private text;
    /** When set, the underlying button will be rendered as an `<a>` with this `href` instead of a `<button>`. */
    href: string;
    /** Tells the browser where to open the link. Only used when `href` is set. */
    target: '_blank' | '_parent' | '_self' | '_top';
    position: 'top-left' | 'top-right';
    closestElement(selector: any, el: any): any;
    componentDidLoad(): void;
    render(): any;
}
