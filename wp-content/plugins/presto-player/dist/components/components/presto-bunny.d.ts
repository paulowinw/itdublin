import type { Components, JSX } from "../types/components";

interface PrestoBunny extends Components.PrestoBunny, HTMLElement {}
export const PrestoBunny: {
    prototype: PrestoBunny;
    new (): PrestoBunny;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
