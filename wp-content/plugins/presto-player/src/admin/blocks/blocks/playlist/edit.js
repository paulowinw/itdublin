/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { PrestoPlaylistUi } from "@presto-player/components-react";
const semverCompare = require("semver/functions/compare");
import {
  store as blockEditorStore,
  InspectorControls,
  useBlockProps,
  useInnerBlocksProps,
  ContrastChecker,
  __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
  __experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
  __experimentalUseBorderProps as useBorderProps,
  __experimentalUseColorProps as useColorProps,
} from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import {
  __experimentalNumberControl as NumberControl,
  PanelBody,
  TextControl,
  Placeholder,
} from "@wordpress/components";
import PlayListPlaceholder from "../playlist-list-item/Placeholder";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { ToggleControl } from "@wordpress/components";

const ALLOWED_BLOCKS = [
  "presto-player/playlist-preview",
  "presto-player/playlist-list",
];

export default ({ attributes, setAttributes, clientId }) => {
  const {
    heading,
    listTextSingular,
    listTextPlural,
    highlightColor,
    transitionDuration,
    selectedItem,
    matchPlaylistToPlayerColor,
  } = attributes;

  const { replaceInnerBlocks } = useDispatch(blockEditorStore);

  const blockProps = useBlockProps({
    css: css`
      wp-block,
      [data-block] {
        margin: 0 !important;
        max-width: none !important;
      }
    `,
  });

  const { selectedBlock, playlistBlocks, playListWrapper } = useSelect(
    (select) => {
      const innerBlocks = select(blockEditorStore).getBlocks(clientId);
      const playListWrapper = (innerBlocks || []).find(
        (block) => block.name === "presto-player/playlist-list"
      );
      const previewWrapper = (innerBlocks || []).find(
        (block) => block.name === "presto-player/playlist-preview"
      );
      return {
        selectedBlock: select(blockEditorStore).getSelectedBlock(),
        previewWrapper,
        playlistBlocks: select(blockEditorStore).getBlocks(
          playListWrapper?.clientId
        ),
        previewBlocks: select(blockEditorStore).getBlocks(
          previewWrapper?.clientId
        ),
        playListWrapper,
      };
    },
    [clientId]
  );

  // when playlist item is selected, update the preview
  useEffect(() => {
    if (
      !selectedBlock?.attributes?.id ||
      selectedBlock.name !== "presto-player/playlist-list-item"
    )
      return;
    setAttributes({
      selectedItem: parseInt(selectedBlock?.attributes?.id),
    });
  }, [playlistBlocks, selectedBlock?.attributes?.id]);

  // if there is no selected item, use the first item
  useEffect(() => {
    if (!selectedItem) {
      const id = parseInt(playlistBlocks?.[0]?.attributes?.id);
      if (!isNaN(id)) {
        setAttributes({
          selectedItem: parseInt(playlistBlocks?.[0]?.attributes?.id),
        });
      }
    }
  }, [playlistBlocks]);

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    renderAppender: false,
    allowedBlocks: ALLOWED_BLOCKS,
    template: [
      ["presto-player/playlist-preview"],
      ["presto-player/playlist-list"],
    ],
    templateLock: "all",
  });

  const colorGradientSettings = useMultipleOriginColorsAndGradients();
  const borderProps = useBorderProps(attributes);
  const colorProps = useColorProps(attributes);

  if (playlistBlocks.length === 0) {
    return (
      <div {...blockProps}>
        <Placeholder
          icon={
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="playlist-block-icon"
              width="24"
              height="24"
              viewBox="0 0 24 24"
            >
              <path
                fill="currentColor"
                d="M3 10h11v2H3zm0-4h11v2H3zm0 8h7v2H3zm13-1v8l6-4z"
              />
            </svg>
          }
          instructions={__(
            "Add a Playlist Item to get started.",
            "presto-player"
          )}
          label={__("Presto Player Playlist", "presto-player")}
          isColumnLayout
        >
          <PlayListPlaceholder
            css={css`
              max-width: 600px;
            `}
            setAttributes={({ id, title }) => {
              setAttributes({ selectedItem: id });
              replaceInnerBlocks(playListWrapper?.clientId, [
                createBlock("presto-player/playlist-list-item", {
                  id,
                  title,
                }),
              ]);
            }}
          />
        </Placeholder>
      </div>
    );
  }

  return (
    <>
      <InspectorControls group="color">
        <ColorGradientSettingsDropdown
          __experimentalIsRenderedInSidebar
          settings={[
            {
              colorValue: highlightColor,
              label: __("Highlight Color"),
              onColorChange: (highlightColor) =>
                setAttributes({ highlightColor }),
              resetAllFilter: () =>
                setAttributes({ highlightColor: undefined }),
            },
          ]}
          {...colorGradientSettings}
          panelId={clientId}
          gradients={[]}
          disableCustomGradients={true}
        />
        <ContrastChecker
          backgroundColor={highlightColor}
          textColor={"#ffffff"}
        />
        {semverCompare(prestoPlayer?.proVersion, "2.0.4") >= 0 &&
          !!highlightColor && (
            <ToggleControl
              label={__("Pass highlight color to player", "presto-player")}
              help={__(
                "Use the playlist highlight color as player color in case your playlist color is different from your brand.",
                "presto-player"
              )}
              checked={matchPlaylistToPlayerColor}
              onChange={(matchPlaylistToPlayerColor) =>
                setAttributes({
                  matchPlaylistToPlayerColor,
                })
              }
              css={css`
                min-width: 250px;
              `}
            />
          )}
      </InspectorControls>

      <InspectorControls>
        <PanelBody title={__("General", "presto-player")} initialOpen={true}>
          <TextControl
            label={__("Playlist Title", "presto-player")}
            value={heading}
            onChange={(heading) => setAttributes({ heading })}
          />

          <TextControl
            label={__("Playlist Items Text - Singular", "presto-player")}
            value={listTextSingular}
            onChange={(listTextSingular) => setAttributes({ listTextSingular })}
          />

          <TextControl
            label={__("Playlist Items Text - Plural", "presto-player")}
            value={listTextPlural}
            onChange={(listTextPlural) => setAttributes({ listTextPlural })}
          />

          <NumberControl
            label={__("Transition Duration (Seconds)", "presto-player")}
            value={transitionDuration}
            onChange={(transitionDuration) =>
              setAttributes({ transitionDuration })
            }
            max={30}
            min={0.5}
            step={0.1}
          />
        </PanelBody>
      </InspectorControls>

      <div
        style={{
          "--presto-playlist-highlight-color": highlightColor,
          "--presto-player-highlight-color": matchPlaylistToPlayerColor
            ? highlightColor
            : "",
          "--presto-playlist-background-color":
            colorProps?.style?.backgroundColor,
          "--presto-playlist-text-color": colorProps?.style?.color,
          "--presto-playlist-border-color": borderProps?.style?.borderColor,
          "--presto-playlist-border-width": borderProps?.style?.borderWidth,
          "--presto-playlist-border-radius": borderProps?.style?.borderRadius,
          border: "none",
        }}
        css={css`
          width: 100%;
          .wp-block-video {
            margin: 0 !important;
          }
        `}
      >
        <PrestoPlaylistUi {...innerBlocksProps} />
      </div>
    </>
  );
};
