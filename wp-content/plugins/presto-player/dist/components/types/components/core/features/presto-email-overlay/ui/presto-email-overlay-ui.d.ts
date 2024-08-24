import { EventEmitter } from '../../../../../stencil-public-runtime';
import { i18nConfig } from '../../../../../interfaces';
export declare class EmailOverlayUI {
    private textInput?;
    /**
     * Props
     */
    headline: string;
    bottomText: string;
    buttonText: string;
    allowSkip: boolean;
    borderRadius: number;
    isLoading: boolean;
    errorMessage: string;
    direction?: 'rtl';
    i18n: i18nConfig;
    provider: string;
    type: string;
    /**
     * State
     */
    email: string;
    isAudioProvider: boolean;
    /**
     * Events
     */
    submitForm: EventEmitter<object>;
    skip: EventEmitter<object>;
    /**
     * Handle form submission
     * @param e Event
     */
    handleSubmit(e: any): void;
    componentDidLoad(): void;
    /**
     * Handle input change
     * @param e Event
     */
    handleChange(e: any): void;
    render(): any;
}
