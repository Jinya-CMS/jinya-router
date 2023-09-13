import React from 'react';
import clsx from 'clsx';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Easy to Use',
    description: (
      <>
        Jinya Router exists of only a handful of attributes and needs only a single method to handle requests.
      </>
    ),
  },
  {
    title: 'Fast and proven base',
    description: (
      <>
        It is built around FastRoute, one of the fastest route matchers for PHP. The request handling is built with the
        battle tested Laminas components.
      </>
    ),
  },
  {
    title: 'Easy to extend',
    description: (
      <>
        You need more features? No problem, Jinya Router allows to manipulate the routing table and add custom PSR-15
        middlewares.
      </>
    ),
  },
];

function Feature({title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
