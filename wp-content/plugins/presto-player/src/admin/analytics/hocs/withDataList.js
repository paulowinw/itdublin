/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { useState } = wp.element;
const { createHigherOrderComponent } = wp.compose;

/**
 * Higher order component factory
 *
 * @return {Function} The higher order component.
 */
export default () =>
  createHigherOrderComponent(
    (WrappedComponent) => (props) => {
      const [loading, setLoading] = useState(false);
      const [page, setPage] = useState(1);
      const [total, setTotal] = useState(0);
      const [totalPages, setTotalPages] = useState(0);
      const [data, setData] = useState([]);
      const [error, setError] = useState("");

      // fetch data
      // we could also abstract this function to make it reusable
      // or do a higher order component
      const fetchData = async ({ endpoint, params = {} }) => {
        setLoading(true);

        let responseData;
        try {
          responseData = await apiFetch({
            path: wp.url.addQueryArgs(endpoint, {
              page,
              per_page: 5,
              ...params,
            }),
            parse: false,
          }).then((res) => {
            const total = res.headers
              ? parseInt(res.headers.get("X-WP-Total"))
              : 0;
            const totalPages = res.headers
              ? parseInt(res.headers.get("X-WP-TotalPages"))
              : 0;
            setTotal(total);
            setTotalPages(totalPages);
            return res.json();
          });

          setData(responseData);
        } catch (e) {
          console.error(e);
          if (e?.message) {
            setError(e.message);
          }
        } finally {
          setLoading(false);
        }
      };

      return (
        <WrappedComponent
          loading={loading}
          setLoading={setLoading}
          fetchData={fetchData}
          page={page}
          setPage={setPage}
          total={total}
          setTotal={setTotal}
          totalPages={totalPages}
          setTotalPages={setTotalPages}
          data={data}
          setData={setData}
          error={error}
          setError={setError}
          {...props}
        />
      );
    },
    "withDataList"
  );
