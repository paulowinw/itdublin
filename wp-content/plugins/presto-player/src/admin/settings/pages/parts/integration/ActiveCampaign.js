import { __ } from "@wordpress/i18n";
import { BaseControl, PanelRow, Notice } from "@wordpress/components";
import { compose } from "@wordpress/compose";

import Integration from "../../../components/Integration";
import TextControl from "../../../components/TextControl";
import withIntegration from "./withIntegration";

export default compose([
  withIntegration({ name: "presto_player_activecampaign" }),
])(
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
    const setData = (props) => {
      updateSetting({
        ...props,
      });
    };

    const onConnect = () => {
      makeRequest({
        path: "/presto-player/v1/activecampaign/connect",
        data: { api_key: setting?.api_key, url: setting?.url },
        message: __("Connected", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    const onDisconnect = async () => {
      makeRequest({
        path: "/presto-player/v1/activecampaign/disconnect",
        message: __("Disconnected", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    return (
      <Integration
        title={__("ActiveCampaign")}
        connected={setting?.connected}
        onConnect={onConnect}
        onDisconnect={onDisconnect}
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
              label={__("Your ActiveCampaign Url", "presto-player")}
              type="url"
              help={__(
                "You can find this on your Settings > Developer page.",
                "presto-player"
              )}
              value={setting?.url}
              onChange={(url) => updateSetting({ url })}
            />
            <TextControl
              label={__("Your ActiveCampaign API key", "presto-player")}
              help={__(
                "You can find this on your Settings > Developer page.",
                "presto-player"
              )}
              value={setting?.api_key}
              onChange={(api_key) => updateSetting({ api_key })}
            />
          </BaseControl>
        </PanelRow>
      </Integration>
    );
  }
);
