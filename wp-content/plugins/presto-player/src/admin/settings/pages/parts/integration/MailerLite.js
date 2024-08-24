import {
  BaseControl,
  ExternalLink,
  Notice,
  PanelRow,
} from "@wordpress/components";
import { compose } from "@wordpress/compose";
import { __ } from "@wordpress/i18n";

import Integration from "../../../components/Integration";
import TextControl from "../../../components/TextControl";
import withIntegration from "./withIntegration";

export default compose([withIntegration({ name: "presto_player_mailerlite" })])(
  ({
    success,
    setSuccess,
    error,
    setError,
    isBusy,
    makeRequest,
    setting,
    updateSetting,
  }) => {
    const { api_key, connected } = setting || {};

    const setData = (props) => {
      updateSetting({
        ...props,
      });
    };

    const onConnect = () => {
      makeRequest({
        path: "/presto-player/v1/mailerlite/connect",
        data: { api_key },
        message: __("Connected", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    const onDisconnect = async () => {
      makeRequest({
        path: "/presto-player/v1/mailerlite/disconnect",
        message: __("Disconnected", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    return (
      <Integration
        title={__("Mailerlite")}
        connected={connected}
        onDisconnect={onDisconnect}
        onConnect={onConnect}
        isBusy={isBusy}
      >
        {error && (
          <PanelRow>
            <Notice
              className="presto-notice"
              status="error"
              onRemove={() => setError("")}
            >
              {error}
            </Notice>
          </PanelRow>
        )}
        {success && (
          <PanelRow>
            <Notice
              className="presto-notice"
              status="success"
              onRemove={() => setSuccess("")}
            >
              {success}
            </Notice>
          </PanelRow>
        )}
        <PanelRow>
          <BaseControl>
            <TextControl
              className="presto-player__setting--mailerlite-api_key"
              label={__("Your MailerLite API key", "presto-player")}
              help={
                <p>
                  {__(
                    "You can create a new key on your MailerLite account page.",
                    "presto-player"
                  )}{" "}
                  <ExternalLink href="https://app.mailerlite.com/integrations/api/">
                    {__("Get My API Key", "presto-player")}
                  </ExternalLink>
                </p>
              }
              value={api_key}
              onChange={(api_key) => updateSetting({ api_key })}
            />
          </BaseControl>
        </PanelRow>
      </Integration>
    );
  }
);
