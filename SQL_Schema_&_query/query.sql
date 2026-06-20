-- All tables in this schema
select table_name
  from user_tables
 order by table_name;

--All constraints mapped to tables
select table_name,
       constraint_name,
       decode(
          constraint_type,
          'P',
          'PK',
          'R',
          'FK',
          'U',
          'UNIQUE',
          'C',
          'CHECK',
          constraint_type
       ) as c_type,
       search_condition
  from user_constraints
 where table_name in ( 'USERS',
                       'PRODUCTS',
                       'BARGAINS',
                       'RATINGS',
                       'WISHLISTS',
                       'REPORTS' )
   and constraint_name not like 'SYS_%'
 order by table_name,
          c_type;


--Product catalogue: product + seller + category + status
select p.id,
       p.title,
       u.name as seller,
       c.category_name as category,
       p.product_condition as cond,
       p.min_proposed_price as min_price,
       p.status
  from products p
  join users u
on p.seller_id = u.id
  join categories c
on p.category_id = c.id
 order by p.id;

--view_all_products: AVAILABLE products with highest live bid
select *
  from view_all_products
 order by max_current_bid desc;

--Bidders on the Dell laptop, highest bid first
select u.name as bidder,
       b.bid_amount,
       b.bid_status
  from bargains b
  join users u
on b.buyer_id = u.id
  join products p
on b.product_id = p.id
 where p.title = 'Dell Latitude 5490 Laptop'
 order by b.bid_amount desc;

--Completed transactions: product, seller, buyer, final price
select t.id,
       p.title as product,
       s.name as seller,
       b.name as buyer,
       t.final_price,
       t.transaction_date
  from transactions t
  join products p
on t.product_id = p.id
  join users s
on p.seller_id = s.id
  join users b
on t.buyer_id = b.id
 order by t.id;

--Seller reputation (view_seller_ratings joined to user name)
select u.name as seller,
       v.avg_seller_rating,
       v.total_reviews
  from view_seller_ratings v
  join users u
on v.seller_id = u.id
 order by v.avg_seller_rating desc;

--Wishlists per user
select u.name as owner,
       p.title as product,
       p.status
  from wishlists w
  join users u
on w.user_id = u.id
  join products p
on w.product_id = p.id
 order by u.name;

--who reported whom, about which product
select r1.name as reporter,
       r2.name as reported,
       p.title as product,
       rep.report_type,
       rep.reason
  from reports rep
  join users r1
on rep.reporter_id = r1.id
  join users r2
on rep.reported_id = r2.id
  join products p
on rep.product_id = p.id;

--Number of products per category
select c.category_name,
       count(p.id) as product_count
  from categories c
  left join products p
on p.category_id = c.id
 group by c.category_name
 order by product_count desc,
          c.category_name;

--non student email is rejected
insert into users (
   id,
   name,
   email,
   password_hash
) values ( users_id_seq.nextval,
           'Outsider',
           'hacker@gmail.com',
           'x' );


--a user cannot rate themselves
insert into ratings (
   id,
   product_id,
   rater_id,
   rated_user_id,
   rating_type,
   rating_value
)
   select ratings_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          'SELLER_RATING',
          5
     from dual;

--rating must be 1..5
insert into ratings (
   id,
   product_id,
   rater_id,
   rated_user_id,
   rating_type,
   rating_value
)
   select ratings_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          'SELLER_RATING',
          6
     from dual;

--invalid bid status
insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'ayesha@stud.kuet.ac.bd'
          ),
          25000,
          'MAYBE'
     from dual;

--same product wishlisted twice
insert into wishlists (
   id,
   user_id,
   product_id
)
   select wishlists_id_seq.nextval,
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          (
             select id
               from products
              where title = 'Redmi Note 12 Mobile'
          )
     from dual;


--duplicate email
insert into users (
   id,
   name,
   email,
   password_hash
) values ( users_id_seq.nextval,
           'Clone',
           'shomik@stud.kuet.ac.bd',
           'x' );


--category that does not exist
insert into products (
   id,
   seller_id,
   category_id,
   title,
   product_condition,
   min_proposed_price
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          9999,
          'Orphan Product',
          'OLD',
          500
     from dual;