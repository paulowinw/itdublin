/**
 * WordPress dependencies
 */
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
      const [stat, setStat] = useState([]);
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
              ...params,
            }),
          });
          setStat(responseData);
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
          stat={stat}
          setStat={setStat}
          error={error}
          setError={setError}
          {...props}
        />
      );
    },
    "withStat"
  );
