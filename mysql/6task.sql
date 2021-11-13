-- 1
SELECT
    sum( amount ),
    sum( likes ),
    concat(
            object_id,
            '__',
            DATE_FORMAT( time_created, '%Y-%m-%d %H:%i' )) AS object_and_date
FROM
    `analytics`
WHERE
    action = 'buy'
  AND object = 'boosterpack'
  AND time_created > DATE_ADD( now(), INTERVAL - 30 DAY )
GROUP BY
    (
    concat(
    object_id,
    '__',
    DATE_FORMAT( time_created, '%Y-%m-%d %H:%i' )));

-- 2
SELECT
    u.wallet_balance,
    u.likes_balance,
    a.refills,
    a.total_likes
FROM
    `user` u
        JOIN (
        SELECT
            user_id,
            sum(
                    IF
                        ( action = 'refill', amount, 0 )) AS refills,
            sum( likes ) AS total_likes
        FROM
            analytics
        GROUP BY
            user_id
    ) a ON u.id = a.user_id
WHERE
        u.id = 2;