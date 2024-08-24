import {
  Spinner,
  Button,
  MenuGroup,
  MenuItem,
  Dropdown,
  Disabled,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { SearchControl } from "@wordpress/components";
import { css } from "@emotion/core";

const EntitySearchDropdown = ({
  options,
  isLoading,
  hasMore,
  search,
  onSearch,
  onSelect,
  onNextPage,
  onCreate,
  onOpen,
  renderItem = null,
  ...dropdownProps
}) => {
  const renderContent = () => {
    if (isLoading && !options.length) return <Spinner />;
    if (!options.length) {
      return (
        <>
          <span
            css={css`
             margin: 8px;
            `}
          >
            {__("None found.", "presto-player")}
          </span>
        </>
      );
    }
    return (
      <>
        {!!onCreate && (
          <MenuGroup>
            <MenuItem
              icon={
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  css={css`
                    width: 18px;
                  `}
                >
                  <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                </svg>
              }
              iconPosition="left"
              onClick={onCreate}
            >
              {__("Add New", "presto-player")}
            </MenuItem>
          </MenuGroup>
        )}

        {!options.length && search ? (
          <Disabled>
            <MenuItem>{__("None found.", "presto-player")}</MenuItem>
          </Disabled>
        ) : (
          <MenuGroup>
            {(options || []).map((item) => {
              if (renderItem) return renderItem({ item, onSelect });
              return (
                <MenuItem
                  icon={item?.icon}
                  iconPosition="left"
                  onClick={() => onSelect(item)}
                  {...item}
                >
                  {item?.title || "Untitled"}
                </MenuItem>
              );
            })}
          </MenuGroup>
        )}
        {hasMore && options.length && (
          <div
            css={css`
              margin-top: 20px;
              text-align: center;
              display: flex;
              justify-content: center;
              width: 100%;
            `}
          >
            <Button
              variant="secondary"
              size={"small"}
              onClick={onNextPage}
              isBusy={isLoading}
            >
              {__("Load More", "presto-player")}
            </Button>
          </div>
        )}
      </>
    );
  };

  return (
    <div
      class="pp_search_dropdown_container"
      css={css`
        width: 100%;
        position: relative;
      `}
    >
      <Dropdown
        onToggle={(open) => onOpen(open)}
        renderContent={() => (
          <div
            css={css`
              width: 500px;
              max-width: 100vw;
              .components-menu-group {
                padding: 8px;
                margin-top: 0;
                margin-bottom: 0;
                margin-left: -8px;
                margin-right: -8px;
              }
              .components-menu-group + .components-menu-group {
                margin-top: 0;
                border-top: 1px solid #ccc;
                padding: 8px;
              }
              .components-menu-group:last-child {
                margin-bottom: -8px;
              }
              .components-menu-group:first-child {
                margin-top: -8px;
              }
            `}
          >
            <SearchControl
              placeholder={__("Search...", "presto-player")}
              value={search}
              onChange={onSearch}
              css={css`
                padding: 0.5em 0.5em 0em 0.5em;
              `}
            />
            {renderContent()}
          </div>
        )}
        {...dropdownProps}
      />
    </div>
  );
};

export default EntitySearchDropdown;
