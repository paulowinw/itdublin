import type { Components, JSX } from "../types/components";

interface PrestoTimestamp extends Components.PrestoTimestamp, HTMLElement {}
export const PrestoTimestamp: {
    prototype: PrestoTimestamp;
    new (): PrestoTimestamp;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
