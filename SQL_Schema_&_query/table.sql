drop view view_all_products;
drop view view_seller_ratings;

drop table reports cascade constraints;
drop table wishlists cascade constraints;
drop table transactions cascade constraints;
drop table ratings cascade constraints;
drop table bargains cascade constraints;
drop table product_images cascade constraints;
drop table products cascade constraints;
drop table categories cascade constraints;
drop table users cascade constraints;

drop sequence users_id_seq;
drop sequence categories_id_seq;
drop sequence products_id_seq;
drop sequence product_images_id_seq;
drop sequence bargains_id_seq;
drop sequence ratings_id_seq;
drop sequence transactions_id_seq;
drop sequence wishlists_id_seq;
drop sequence reports_id_seq;

create sequence users_id_seq start with 1 increment by 1;
create sequence categories_id_seq start with 1 increment by 1;
create sequence products_id_seq start with 1 increment by 1;
create sequence product_images_id_seq start with 1 increment by 1;
create sequence bargains_id_seq start with 1 increment by 1;
create sequence ratings_id_seq start with 1 increment by 1;
create sequence transactions_id_seq start with 1 increment by 1;
create sequence wishlists_id_seq start with 1 increment by 1;
create sequence reports_id_seq start with 1 increment by 1;

create table users (
   id            number(20) primary key,
   name          varchar2(100) not null,
   email         varchar2(150) unique not null,
   password_hash varchar2(255) not null,
   mobile_no     varchar2(20),
   created_at    timestamp default current_timestamp,
   constraint chk_kuet_email check ( email like '%@stud.kuet.ac.bd' )
);

create table categories (
   id            number(10) primary key,
   category_name varchar2(100) unique not null
);

create table products (
   id                 number(20) primary key,
   seller_id          number(20) not null,
   category_id        number(10) not null,
   title              varchar2(150) not null,
   description        varchar2(1000),
   product_condition  varchar2(20) check ( product_condition in ( 'NEW',
                                                                 'OLD' ) ),
   min_proposed_price number(10,2) not null,
   status             varchar2(20) default 'AVAILABLE',
   created_at         timestamp default current_timestamp,
   constraint fk_prod_seller foreign key ( seller_id )
      references users ( id )
         on delete cascade,
   constraint fk_prod_category foreign key ( category_id )
      references categories ( id ),
   constraint chk_prod_status
      check ( status in ( 'AVAILABLE',
                          'SOLD',
                          'UNAVAILABLE' ) )
);

create table product_images (
   id         number(20) primary key,
   product_id number(20) not null,
   image_path varchar2(255) not null,
   constraint fk_image_prod foreign key ( product_id )
      references products ( id )
         on delete cascade
);

create table bargains (
   id         number(20) primary key,
   product_id number(20) not null,
   buyer_id   number(20) not null,
   bid_amount number(10,2) not null,
   bid_status varchar2(20) default 'PENDING',
   created_at timestamp default current_timestamp,
   constraint fk_barg_prod foreign key ( product_id )
      references products ( id )
         on delete cascade,
   constraint fk_barg_buyer foreign key ( buyer_id )
      references users ( id )
         on delete cascade,
   constraint chk_bid_status
      check ( bid_status in ( 'PENDING',
                              'ACCEPTED',
                              'REJECTED' ) )
);

create table ratings (
   id            number(20) primary key,
   product_id    number(20) not null,
   rater_id      number(20) not null,
   rated_user_id number(20) not null,
   rating_type   varchar2(20) not null,
   rating_value  number(2,1) not null,
   review_text   varchar2(500),
   created_at    timestamp default current_timestamp,
   constraint fk_rating_prod foreign key ( product_id )
      references products ( id ),
   constraint fk_rating_rater foreign key ( rater_id )
      references users ( id ),
   constraint fk_rating_rated foreign key ( rated_user_id )
      references users ( id ),
   constraint chk_rating_type check ( rating_type in ( 'BUYER_RATING',
                                                       'SELLER_RATING' ) ),
   constraint chk_rating_value check ( rating_value between 1 and 5 ),
   constraint chk_not_self_rating check ( rater_id != rated_user_id )
);

create table transactions (
   id               number(20) primary key,
   product_id       number(20) unique not null,
   buyer_id         number(20) not null,
   final_price      number(10,2) not null,
   transaction_date timestamp default current_timestamp,
   constraint fk_tran_prod foreign key ( product_id )
      references products ( id ),
   constraint fk_tran_buyer foreign key ( buyer_id )
      references users ( id )
);

create table wishlists (
   id         number(20) primary key,
   user_id    number(20) not null,
   product_id number(20) not null,
   created_at timestamp default current_timestamp,
   constraint fk_wish_user foreign key ( user_id )
      references users ( id )
         on delete cascade,
   constraint fk_wish_prod foreign key ( product_id )
      references products ( id )
         on delete cascade,
   constraint uq_user_product unique ( user_id,
                                       product_id )
);

create table reports (
   id          number(20) primary key,
   reporter_id number(20) not null,
   reported_id number(20) not null,
   product_id  number(20) not null,
   report_type varchar2(20) not null,
   reason      varchar2(500) not null,
   created_at  timestamp default current_timestamp,
   constraint fk_rep_reporter foreign key ( reporter_id )
      references users ( id ),
   constraint fk_rep_reported foreign key ( reported_id )
      references users ( id ),
   constraint fk_rep_prod foreign key ( product_id )
      references products ( id ),
   constraint chk_rep_type check ( report_type in ( 'BUYER',
                                                    'SELLER' ) )
);

create or replace view view_seller_ratings as
   select rated_user_id as seller_id,
          round(
             avg(rating_value),
             2
          ) as avg_seller_rating,
          count(id) as total_reviews
     from ratings
    where rating_type = 'SELLER_RATING'
    group by rated_user_id;

create or replace view view_all_products as
   select p.id as product_id,
          p.title,
          c.category_name,
          p.product_condition,
          p.status,
          p.min_proposed_price,
          coalesce(
             max(b.bid_amount),
             0
          ) as max_current_bid
     from products p
     join categories c
   on p.category_id = c.id
     left join bargains b
   on p.id = b.product_id
      and b.bid_status != 'REJECTED'
    where p.status = 'AVAILABLE'
    group by p.id,
             p.title,
             c.category_name,
             p.product_condition,
             p.status,
             p.min_proposed_price;