import type { Components, JSX } from "../types/components";

interface PrestoPlayerSkeleton extends Components.PrestoPlayerSkeleton, HTMLElement {}
export const PrestoPlayerSkeleton: {
    prototype: PrestoPlayerSkeleton;
    new (): PrestoPlayerSkeleton;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
