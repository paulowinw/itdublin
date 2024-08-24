/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  BaseControl,
  Button,
  Flex,
  Icon,
  PanelBody,
} from "@wordpress/components";
import { check, symbol } from "@wordpress/icons";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import VideoBranding from "@/admin/blocks/shared/branding";
import VideoChapters from "@/admin/blocks/shared/chapters";
import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import VideoOverlays from "@/admin/blocks/shared/overlays";
import VideoPresets from "@/admin/blocks/shared/presets";
import VideoSettings from "@/admin/blocks/shared/settings";
import InserterShortcodeInput from "../plugins/reusable-videos/ShortcodeInput";
import EditContext from "../blocks/reusable-display/context";
import { useContext } from "@wordpress/element";

export default function ({ attributes, setAttributes }) {
  const { isEditing, setIsEditing } = useContext(EditContext);

  const userCanReadSettings = useSelect((select) =>
    select(coreStore).canUser("read", "settings")
  );

  return (
    <>
      {isEditing && (
        <PanelBody>
          <Flex align="center" justify="flex-start">
            <Icon icon={symbol} />
            <h2 class="block-editor-block-card__title">
              {__("Editing Synced Media", "presto-player")}
            </h2>
          </Flex>

          <BaseControl
            help={__(
              "You are currently editing a synced media hub item that may be reused across your site.",
              "presto-player"
            )}
            css={css`
              margin-bottom: 10px !important;
            `}
          ></BaseControl>

          <Button
            icon={check}
            onClick={() => setIsEditing(false)}
            variant="secondary"
          >
            {__("Done Editing", "presto-player")}
          </Button>
        </PanelBody>
      )}
      <PanelBody
        title={
          <>
            {__("Chapters", "presto-player")}{" "}
            {!prestoPlayer?.isPremium && <ProBadge />}
          </>
        }
        initialOpen={prestoPlayer?.isPremium}
      >
        <VideoChapters setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>
      <PanelBody
        title={
          <>
            {__("Overlays", "presto-player")}{" "}
            {!prestoPlayer?.isPremium && <ProBadge />}
          </>
        }
        initialOpen={prestoPlayer?.isPremium}
      >
        <VideoOverlays setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody title={__("Video settings", "presto-player")}>
        <VideoSettings setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody title={__("Video Preset", "presto-player")}>
        <VideoPresets setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <InserterShortcodeInput />

      {!!userCanReadSettings && (
        <PanelBody
          title={__("Global Player Branding", "presto-player")}
          initialOpen={false}
        >
          <VideoBranding
            setAttributes={setAttributes}
            attributes={attributes}
          />
        </PanelBody>
      )}
    </>
  );
}
