/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import {
  ToggleControl,
  BaseControl,
  RangeControl,
  TextControl,
  Button,
} from "@wordpress/components";
import { chevronDown, chevronUp } from "@wordpress/icons";
import { useEffect, useState } from "@wordpress/element";

export default ({ state, updateState, className }) => {
  const { search } = state;
  const [showAdvanced, setShowAdvanced] = useState(false);

  const defaults = {
    enabled: false,
    minMatchCharLength: 1,
    threshold: 0.3,
    placeholder: "Search",
  };
  useEffect(() => {
    Object.keys(defaults).forEach((key) => {
      if (state?.search?.[key] === undefined) {
        updateSearchState({
          [key]: defaults[key],
        });
      }
    });
  }, [state]);

  const updateSearchState = (updated) => {
    updateState({
      ...state,
      search: {
        ...search,
        ...updated,
      },
    });
  };

  return (
    <div className={className}>
      <BaseControl>
        <h3>{__("Searchable Captions", "presto-player")}</h3>
      </BaseControl>
      <BaseControl className="presto-player__control--search">
        <ToggleControl
          label={__("Enable", "presto-player")}
          help={__(
            "Show a search bar on your player which enables searching within the subtitles of the video.",
            "presto-player"
          )}
          onChange={(enabled) => {
            updateSearchState({
              enabled,
            });
          }}
          checked={search?.enabled}
        />
      </BaseControl>
      {search?.enabled && (
        <div>
          <BaseControl className="presto-player__control--placeholder-text">
            <TextControl
              label={__("Placeholder Text", "presto-player")}
              help=""
              value={search?.placeholder}
              onChange={(placeholder) => updateSearchState({ placeholder })}
            />
          </BaseControl>

          <BaseControl>
            <Button
              onClick={() => setShowAdvanced(!showAdvanced)}
              iconPosition="right"
              icon={showAdvanced ? chevronUp : chevronDown}
              variant="link"
            >
              {__("Advanced Settings", "presto-player")}
            </Button>
          </BaseControl>

          {!!showAdvanced && (
            <>
              <BaseControl>
                <RangeControl
                  label={__(
                    "Minimum Matching Character Length",
                    "presto-player"
                  )}
                  help={__(
                    "Only the matches whose length exceeds this value will be returned. (For instance, if you want to ignore single character matches in the result, set it to 2",
                    "presto-player"
                  )}
                  value={search?.minMatchCharLength || 1}
                  onChange={(minMatchCharLength) =>
                    updateSearchState({ minMatchCharLength })
                  }
                  min={0}
                  max={10}
                />
              </BaseControl>
              <BaseControl>
                <RangeControl
                  label={__("Threshold", "presto-player")}
                  help={__(
                    "At what point does the match algorithm give up. A threshold of 0.0 requires a perfect match (of both letters and location), a threshold of 1.0 would match anything.",
                    "presto-player"
                  )}
                  value={search?.threshold || 1}
                  onChange={(threshold) => updateSearchState({ threshold })}
                  min={0}
                  max={1}
                  step={0.1}
                />
              </BaseControl>
            </>
          )}
        </div>
      )}
    </div>
  );
};
