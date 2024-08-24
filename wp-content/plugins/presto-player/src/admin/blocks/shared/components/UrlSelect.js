/** @jsx jsx */
const { __ } = wp.i18n;
const { Button, Popover, Icon } = wp.components;

const { __experimentalLinkControl: LinkControl } = wp.blockEditor;
const { useState } = wp.element;
const { prependHTTP } = wp.url;

import { css, jsx } from "@emotion/core";

export default ({ setSettings, settings }) => {
  const [visible, setVisible] = useState(false);
  const { url } = settings;

  /**
   * Pending settings to be applied to the next link. When inserting a new
   * link, toggle values cannot be applied immediately, because there is not
   * yet a link for them to apply to. Thus, they are maintained in a state
   * value until the time that the link can be inserted or edited.
   *
   * @type {[Object|undefined,Function]}
   */
  const [nextLinkValue, setNextLinkValue] = useState();

  const linkValue = {
    url: settings?.url,
    type: settings?.type,
    id: settings?.id,
    opensInNewTab: settings?.opensInNewTab,
    ...nextLinkValue,
  };

  const onChangeLink = (nextValue) => {
    // Merge with values from state, both for the purpose of assigning the
    // next state value, and for use in constructing the new link format if
    // the link is ready to be applied.
    nextValue = {
      ...nextLinkValue,
      ...nextValue,
    };

    // LinkControl calls `onChange` immediately upon the toggling a setting.
    const didToggleSetting =
      linkValue.opensInNewTab !== nextValue.opensInNewTab &&
      linkValue.url === nextValue.url;

    // If change handler was called as a result of a settings change during
    // link insertion, it must be held in state until the link is ready to
    // be applied.
    const didToggleSettingForNewLink =
      didToggleSetting && nextValue.url === undefined;

    // If link will be assigned, the state value can be considered flushed.
    // Otherwise, persist the pending changes.
    setNextLinkValue(didToggleSettingForNewLink ? nextValue : undefined);

    if (didToggleSettingForNewLink) {
      return;
    }

    const newUrl = prependHTTP(nextValue.url);
    setSettings({
      url: newUrl,
      type: nextValue.type,
      id:
        nextValue.id !== undefined && nextValue.id !== null
          ? String(nextValue.id)
          : undefined,
      opensInNewTab: nextValue.opensInNewTab,
    });
  };

  const confirmTrash = () => {
    const r = confirm(
      __("Are you sure you wish to remove this link?", "presto-player")
    );
    if (r) {
      setSettings({});
    }
  };

  return (
    <span>
      {url ? (
        <div
          css={css`
            display: flex;
            justify-content: space-between;
          `}
        >
          <div
            css={css`
              max-width: 85%;
              overflow: hidden;
              display: flex;
              align-items: center;
            `}
          >
            <a
              href="#"
              css={css`
                padding: 10px;
                background: #f3f3f3;
                border-radius: 4px;
                width: 100%;
                display: inline-flex;
                align-items: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                text-decoration: none;
              `}
              onClick={() => setVisible(!visible)}
            >
              <Icon
                icon="edit"
                css={css`
                  cursor: pointer;
                  opacity: 0.75;
                  margin: 0 2px;
                  font-size: 16px;
                  width: 16px;
                  height: 16px;
                  text-decoration: none;
                `}
              />
              {url}
            </a>
            {visible && (
              <Popover
                position="bottom center"
                onClose={() => setVisible(false)}
              >
                <LinkControl value={settings} onChange={onChangeLink} />
              </Popover>
            )}
          </div>
          <div
            css={css`
              display: flex;
              align-items: center;
            `}
          >
            <Icon
              onClick={confirmTrash}
              icon="trash"
              className="presto-icon"
              css={css`
                cursor: pointer;
                opacity: 0.75;
                margin: 0 2px;
                fontsize: 18px;
                width: 18px;
                height: 18px;

                &:hover {
                  color: #cc1818;
                }
              `}
            />
          </div>
        </div>
      ) : (
        <span>
          <Button isPrimary isSmall onClick={() => setVisible(!visible)}>
            {__("Add Link", "presto-player")}
          </Button>
          {visible && (
            <Popover css={css`margin-top: 10px`} position="bottom right" onClose={() => setVisible(false)}>
              <LinkControl value={settings} onChange={onChangeLink} />
            </Popover>
          )}
        </span>
      )}
    </span>
  );
};
