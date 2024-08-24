/** @jsx jsx */
import { css, jsx } from "@emotion/core";

import { __ } from "@wordpress/i18n";
import { Notice, ExternalLink, ToggleControl } from "@wordpress/components";
import { useEntityProp } from "@wordpress/core-data";

import Group from "../components/Group";
import Page from "../components/Page";

export default () => {
  const [performance, setPerformance] = useEntityProp(
    "root",
    "site",
    "presto_player_performance"
  );
  const updatePerformance = (data) => {
    setPerformance({
      ...(performance || {}),
      ...data,
    });
  };

  return (
    <Page
      title={__("Performance", "presto-player")}
      description={__("Player performance preferences.", "presto-player")}
    >
      <Group
        title={__("Performance", "presto-player")}
        description={__(
          "Performance options for player loading.",
          "presto-player"
        )}
      >
        <div>
          <ToggleControl
            className={"presto-player__setting--module-enabled"}
            label={__("Dynamically Load JavaScript", "presto-player")}
            help={__(
              "Dynamically load javascript modules on the page which can significantly reduce javascript size and increase performance.",
              "presto-player"
            )}
            checked={performance?.module_enabled}
            onChange={(module_enabled) => updatePerformance({ module_enabled })}
          />

          {!!performance?.module_enabled && (
            <Notice
              css={css`
                background: #f3f4f5 !important;
                margin-bottom: 20px !important;
              `}
              className="presto-notice"
              status="info"
              isDismissible={false}
            >
              <div>
                <strong>{__("Please Note", "presto-player")}</strong>
              </div>
              <div>
                {__(
                  "You may need to exclude the player script from combining, as well as enable CORS requests for some CDNs.",
                  "presto-player"
                )}{" "}
                <ExternalLink href="https://prestoplayer.com/docs/performance-preferences-explained#global-player-performance-setting">
                  {__("Learn More", "presto-player")}
                </ExternalLink>
              </div>
            </Notice>
          )}
        </div>

        <ToggleControl
          className={"presto-player__setting--automations"}
          label={__(
            "Enable Ajax Requests for Progress Integrations",
            "presto-player"
          )}
          help={__(
            "Keep this on unless you do not plan on using automation, LMS or membership integrations.",
            "presto-player"
          )}
          checked={performance?.automations}
          onChange={(automations) => updatePerformance({ automations })}
        />
      </Group>
    </Page>
  );
};
