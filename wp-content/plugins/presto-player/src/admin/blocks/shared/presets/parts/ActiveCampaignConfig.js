/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { SelectControl, Notice } = wp.components;
const { useEffect, useState } = wp.element;
import LoadSelect from "../../components/LoadSelect";

export default ({ options, updateEmailState }) => {
  const [fetchingLists, setFetchingLists] = useState(false);
  const [fetchingTags, setFetchingTags] = useState(false);

  const [lists, setLists] = useState([
    { value: null, label: __("Choose a list", "presto-player") },
  ]);
  const [tags, setTags] = useState([
    { value: null, label: __("Choose a tag", "presto-player") },
  ]);
  const [error, setError] = useState("");

  const fetchLists = async () => {
    setFetchingLists(true);
    try {
      const fetched = await wp.apiFetch({
        path: "presto-player/v1/activecampaign/lists",
      });

      let listOptions = lists;
      (fetched || []).forEach((list) => {
        listOptions = [
          ...listOptions,
          ...[
            {
              value: list.id,
              label: list.name || __("Default list", "presto-player"),
            },
          ],
        ];
      });
      setLists(listOptions);
    } catch (e) {
      if (e?.message) {
        setError(e.message);
      }
    } finally {
      setFetchingLists(false);
    }
  };

  const fetchTags = async () => {
    setFetchingTags(true);
    try {
      const fetched = await wp.apiFetch({
        path: "presto-player/v1/activecampaign/tags",
      });

      let tagOptions = tags;
      (fetched || []).forEach((tag) => {
        tagOptions = [
          ...tagOptions,
          ...[
            {
              value: tag.id,
              label: tag.tag,
            },
          ],
        ];
      });
      setTags(tagOptions);
    } catch (e) {
      if (e?.message) {
        setError(e.message);
      }
    } finally {
      setFetchingTags(false);
    }
  };

  useEffect(() => {
    fetchLists();
    fetchTags();
  }, []);

  if (error) {
    return (
      <Notice className="presto-notice" status="error" isDismissible={false}>
        {error}
      </Notice>
    );
  }

  return (
    <div>
      {fetchingLists ? (
        <LoadSelect />
      ) : (
        lists.length > 1 && (
          <SelectControl
            label={__("Choose a list", "presto-player")}
            value={options?.provider_list}
            options={lists}
            onChange={(provider_list) => updateEmailState({ provider_list })}
          />
        )
      )}

      {fetchingTags ? (
        <LoadSelect />
      ) : (
        tags.length > 1 && (
          <SelectControl
            label={__("Choose a tag", "presto-player")}
            value={options?.provider_tag}
            options={tags}
            onChange={(provider_tag) => updateEmailState({ provider_tag })}
          />
        )
      )}
    </div>
  );
};
