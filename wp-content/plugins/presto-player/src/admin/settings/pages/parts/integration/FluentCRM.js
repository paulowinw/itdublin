import { Notice, PanelRow } from "@wordpress/components";
import { compose } from "@wordpress/compose";
import { __ } from "@wordpress/i18n";

import Integration from "../../../components/Integration";
import withIntegration from "./withIntegration";

export default compose([withIntegration({ name: "presto_player_fluentcrm" })])(
  ({ error, setError, isBusy, makeRequest, setting, updateSetting }) => {
    const setData = (props) => {
      updateSetting({
        ...props,
      });
    };

    const { connected } = setting || {};

    const onConnect = () => {
      makeRequest({
        path: "/presto-player/v1/fluentcrm/connect",
        message: __("Installed and connected", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    const onDisconnect = async () => {
      makeRequest({
        path: "/presto-player/v1/fluentcrm/disconnect",
        message: __("Deactivated", "presto-player"),
        success: setData,
        error: setData,
      });
    };

    return (
      <Integration
        title={__("FluentCRM")}
        connected={connected}
        onConnect={onConnect}
        onDisconnect={onDisconnect}
        connectButtonText={__("Install FluentCRM Plugin", "presto-player")}
        disconnectButtonText={__(
          "Deactivate FluentCRM Plugin",
          "presto-player"
        )}
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
        {connected && (
          <PanelRow>
            <Notice
              className="presto-notice"
              status="success"
              isDismissible={false}
            >
              {__("Installed and connected!", "presto-player")}
            </Notice>
          </PanelRow>
        )}
      </Integration>
    );
  }
);
