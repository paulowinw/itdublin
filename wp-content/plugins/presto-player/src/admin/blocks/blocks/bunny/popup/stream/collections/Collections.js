/** @jsx jsx */
import { css, jsx } from "@emotion/core";

const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { useSelect, dispatch } = wp.data;

import Collection from "./Collection";

export default () => {
  const collections = useSelect((select) =>
    select("presto-player/bunny-popup").collections()
  );
  const type = useSelect((select) =>
    select("presto-player/bunny-popup").requestType()
  );

  const fetchCollections = async () => {
    try {
      const response = await wp.apiFetch({
        path: wp.url.addQueryArgs(`presto-player/v1/bunny/stream/collections`, {
          type,
          items_per_page: 500,
        }),
      });
      dispatch("presto-player/bunny-popup").setCollections(response?.items);
    } catch (e) {
      if (e?.data?.status === 401) {
        return;
      }
      dispatch("presto-player/bunny-popup").addError(e.message);
    }
  };

  useEffect(() => {
    fetchCollections();
  }, []);

  return (
    !!collections.length && (
      <div>
        <h2>{__("Collections", "presto-player")}</h2>
        <div
          css={css`
            display: flex;
            align-items: stretch;
            overflow: auto;
          `}
        >
          {collections.map((collection) => (
            <Collection collection={collection} key={collection.guid} />
          ))}
        </div>
      </div>
    )
  );
};
