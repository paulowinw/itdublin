/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  store as blockEditorStore,
  useBlockProps,
  useInnerBlocksProps,
  BlockControls,
} from "@wordpress/block-editor";
import { useSelect, useDispatch } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import ProvidersPlaceholder from "../../shared/ProvidersPlaceholder/ProvidersPlaceholder";
import {
  Flex,
  Toolbar,
  Button,
  Dropdown,
  MenuItem,
  Icon,
  MenuGroup,
} from "@wordpress/components";
import { symbol, symbolFilled } from "@wordpress/icons";
import { useState } from "@wordpress/element";
import { useEntityProp } from "@wordpress/core-data";

export default ({ clientId }) => {
  const { setTemplateValidity } = useDispatch(blockEditorStore);
  const innerBlocks = useSelect(
    (select) => select(blockEditorStore).getBlock(clientId).innerBlocks
  );
  setTemplateValidity(true);
  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    templateLock: false,
    renderAppender: false,
  });
  const [mediaHubSyncDefault] = useEntityProp(
    "root",
    "site",
    "presto_player_media_hub_sync_default"
  );
  const [sync, setSync] = useState(() => mediaHubSyncDefault);
  if (!innerBlocks?.length) {
    return (
      <div {...innerBlocksProps}>
        {
          <>
            <BlockControls>
              <Toolbar>
                <Dropdown
                  popoverProps={{ placement: "bottom-left" }}
                  renderToggle={({ onToggle }) => (
                    <Flex>
                      <Button
                        onClick={onToggle}
                        css={css`
                          background: transparent;
                          border: none;
                          cursor: pointer;
                          display: flex;
                          justify-content: space-between;
                          align-items: center;
                          gap: 4px;
                        `}
                        icon={
                          sync ? (
                            <Icon icon={symbolFilled} />
                          ) : (
                            <Icon icon={symbol} />
                          )
                        }
                      >
                        {sync
                          ? __("Synced", "presto-player")
                          : __("Not synced", "presto-player")}
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 16 16"
                          fill="currentColor"
                          className="w-4 h-4"
                          width={"16px"}
                        >
                          <path
                            fillRule="evenodd"
                            d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                            clipRule="evenodd"
                          />
                        </svg>
                      </Button>
                    </Flex>
                  )}
                  renderContent={() => (
                    <MenuGroup>
                      <MenuItem
                        onClick={() => setSync(true)}
                        icon={<Icon icon={symbolFilled} />}
                        isSelected={sync}
                        iconPosition="left"
                      >
                        {__("Sync to media hub", "presto-player")}
                      </MenuItem>
                      <MenuItem
                        onClick={() => setSync(false)}
                        icon={<Icon icon={symbol} />}
                        isSelected={!sync}
                        iconPosition="left"
                      >
                        {__("Don't sync to media hub", "presto-player")}
                      </MenuItem>
                    </MenuGroup>
                  )}
                />
              </Toolbar>
            </BlockControls>

            <ProvidersPlaceholder
              clientId={clientId}
              shouldInsertBlock={false}
              selectExisting={true}
              sync={sync}
            />
          </>
        }
      </div>
    );
  }
  return <div {...innerBlocksProps}></div>;
};
