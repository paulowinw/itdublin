/**
 * External dependencies
 */
import classnames from "classnames";

/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";

import { partial, noop, find } from "lodash";

/**
 * WordPress components
 */
import { NavigableMenu, Animate } from "@wordpress/components";

export default ({
  className,
  children,
  items,
  title,
  orientation = "horizontal",
  activeClass = "is-active",
  onSelect = noop,
}) => {
  const [selected, setSelected] = useState(null);
  const [origin, setOrigin] = useState("left");

  const handleClick = (itemKey) => {
    setSelected(itemKey);
    onSelect && onSelect(itemKey);
  };

  const onNavigate = (childIndex, child) => {
    child.click();
  };

  const selectedTab = find(items, { name: selected });
  const selectedId = `${selectedTab?.name ?? "none"}`;

  useEffect(() => {
    setOrigin(selected ? "right" : "left");
  }, [selected]);

  return (
    <div className={className}>
      <Animate type="slide-in" origin={!!selectedTab ? "right" : "left"}>
        {({ className }) =>
          !selectedTab ? (
            <div className={classnames(className, "is-from-right")}>
              {!!title && <h2>{title}</h2>}
              <NavigableMenu
                role="itemlist"
                orientation={orientation}
                onNavigate={onNavigate}
                className={classnames("presto-player__menu-items")}
              >
                {items.map((item) => (
                  <div
                    className={classnames(
                      "presto-player__menu-item",
                      item.className,
                      {
                        [activeClass]: item.name === selected,
                      }
                    )}
                    itemId={`${item.name}`}
                    aria-controls={`${item.name}-view`}
                    selected={item.name === selected}
                    key={item.name}
                    onClick={partial(handleClick, item.name)}
                  >
                    {!!item.icon && (
                      <div class="presto-player__menu-icon">{item.icon}</div>
                    )}
                    {item.title}
                    <svg
                      className="submenu-icon"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      width="24"
                      height="24"
                      role="img"
                      ariaHidden="true"
                      focusable="false"
                    >
                      <path d="M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"></path>
                    </svg>
                  </div>
                ))}
              </NavigableMenu>
            </div>
          ) : (
            <div
              key={selectedId}
              aria-labelledby={selectedId}
              role="itempanel"
              id={`${selectedId}-view`}
              className={classnames(className, "presto-player__menu-content")}
            >
              <div className="presto-player__menu-items">
                <div
                  className="presto-player__menu-item is-back-button"
                  onClick={() => handleClick("")}
                >
                  <svg
                    class="back-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="24"
                    height="24"
                    role="img"
                    aria-hidden="true"
                    focusable="false"
                  >
                    <path d="M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"></path>
                  </svg>
                  {__("Back", "presto-player")}
                </div>
              </div>
              {children(selectedTab)}
            </div>
          )
        }
      </Animate>
    </div>
  );
};
